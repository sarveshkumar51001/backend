<?php
namespace App\Http\Middleware\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Str;
use Closure;
use function slack;

class HandleWebhookDataMiddleware
{

    protected $Webhook;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            $this->Webhook = $this->saveWebhook();

            $request->webhook_id = $this->Webhook->{Webhook::ID};

            $this->dispatchWebhookJob();

            $this->postToSlack($this->Webhook);

            return $next($request);
        } catch (\Exception $e) {
            log_error($e);
            logger($e);
        }

        return response('error', 500);
    }

    private function saveWebhook()
    {
        $request = request();
        $url = Str::replaceFirst('webhook/', '', $request->path());
        $data = explode('/', $url);
        $source = strtolower($data[0]);
        $name = strtolower(implode('_', array_slice($data, 1)));

        $event = sprintf("webhook.%s.%s", $source, $name);

        $data = [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'query' => $request->query->all(),
            'cookies' => $request->cookies->all(),
            'attributes' => $request->attributes->all(),
            'files' => $request->files->all()
        ];

        $Webhook = new Webhook();
        $Webhook->{Webhook::EVENT} = $event;
        $Webhook->{Webhook::NAME} = $name;
        $Webhook->{Webhook::SOURCE} = $source;
        $Webhook->{Webhook::DATA} = $data;
        // $Webhook->{Webhook::ISAUTHENTICATED} = $this->authenticateWebhook();
        $Webhook->{Webhook::CreatedAt} = time();
        $Webhook->save();

        return $Webhook;
    }

    private function dispatchWebhookJob()
    {
        $namespace = '\App\Library\Webhook\Events';
        $source = \Illuminate\Support\Str::title($this->Webhook->{Webhook::SOURCE});
        $class_name = \Illuminate\Support\Str::studly($this->Webhook->{Webhook::NAME});

        $class_path = sprintf("%s\%s\%s", $namespace, $source, $class_name);
        if (class_exists($class_path)) {
            if (method_exists($class_path, 'handle')) {
                \App\Jobs\WebhookEventJob::dispatch($class_path, $this->Webhook);
            }
        }
    }

    private function postToSlack(Webhook $Webhook)
    {
        $source = Str::ucfirst($Webhook->{Webhook::SOURCE});
        $data = array(
            'WEBHOOK_ID' => $Webhook->{Webhook::ID},
            "EVENT" => $Webhook->{Webhook::EVENT},
            "SOURCE" => $source,
            "URL" => request()->fullUrl()
        );

        $title = sprintf("New Incoming Webhook from %s", $source);

        slack($data, $title)->webhook(env('SLACK_WEBHOOK_NOTIFICATION'), null)
            ->info()
            ->post();
    }

    private function authenticateWebhook()
    {
        $source = $this->Webhook->{Webhook::SOURCE};
        $request = request();
        if ($source == 'shopify') {
            $hmac_header = $request->header('x-shopify-hmac-sha256', null);

            $calculated_hmac = base64_encode(hash_hmac('sha256', $request->getContent(), env('SHOPIFY_WEBHOOK_SECRET', null), true));

            return ($hmac_header == $calculated_hmac);
        } elseif ($source == 'instapage') {
            $token = $request->header('Authorization', null);

            if (in_array('page_id', $request->all())) {
                $calculated_token = md5($request->all()['page_id']);
                return ($token == $calculated_token);
            }
        }

        return false;
    }
}
