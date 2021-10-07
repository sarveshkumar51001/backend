<?php
namespace App\Http\Middleware\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Str;
use Closure;
use function slack;
use App\Models\WebhookNotification;
use App\Jobs\WebhookEventJob;

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
            // Checking if blank data was posted
            if (count($request->all()) == 0) {
                return response('No Data Found', 406);
            }

            $this->Webhook = $this->saveWebhook();

            $request->webhook_id = $this->Webhook->{Webhook::ID};

            // Dispatch Webhook if event class exists
            if ($class_path = webhook_event_class($this->Webhook)) {

                WebhookEventJob::dispatch($class_path, $this->Webhook)->onQueue('high');
            } else {
                // Post Default message to Slack Channel
                $this->postToSlack($this->Webhook);
            }

            return $next($request);
        } catch (\Exception $e) {
            log_error($e);
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

        $body = $request->all();
        $body['submission_code'] = strtoupper(uniqid());

        $data = [
            'headers' => $request->headers->all(),
            'body' => $body,
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

        if ($DefaultWebhookURL = WebhookNotification::where('identifier', 'all')->first()) {
            slack($data, $title)->webhook($DefaultWebhookURL['to']['webhook_url'])
                ->info()
                ->post();
        }
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
