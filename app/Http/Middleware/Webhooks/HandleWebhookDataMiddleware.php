<?php
namespace App\Http\Middleware\Webhooks;

use Closure;
use Illuminate\Support\Str;
use App\Models\Webhook;
use App\Library\Shopify;
use App\Library\Slack\Slack;
use App\Models\WebhookNotification;
use App\Library\Webhook\SlackTranslation;

class HandleWebhookDataMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = Str::replaceFirst('webhook/', '', $request->path());
        $data = explode('/', $url);
        $source = $data[0];
        $name = implode('-', array_slice($data, 1));
        $event = printf("webhook.%s.%s", $source, $name);
        $fields = $request->all();

        $Webhook = new Webhook();
        $Webhook->{Webhook::EVENT} = $event;
        $Webhook->{Webhook::NAME} = $name;
        $Webhook->{Webhook::SOURCE} = $source;
        $Webhook->{Webhook::DATA} = $fields;
        $Webhook->{Webhook::ISAUTHENTICATED} = $this->authenticateWebhook($source, $request);
        $Webhook->{Webhook::CreatedAt} = time();
        $Webhook->save();
        
        event($event, $Webhook->toArray());
        $request->webhook_id = $Webhook->{Webhook::ID};

        //$this->postToSlack($fields, $event);

        return $next($request);
    }

    private function authenticateWebhook($source, $request)
    {
        if ($source == 'shopify') {
            $hmac_header = $request->header('x-shopify-hmac-sha256', null);

            $calculated_hmac = base64_encode(hash_hmac('sha256', $request->getContent(), env('SHOPIFY_WEBHOOK_SECRET', null), true));

            return ($hmac_header == $calculated_hmac);
        } elseif ($source == 'instapage') {
            $token = $request->header('Authorization', null);
            $calculated_token = md5($request->all()['page_id']);

            return ($token == $calculated_token);
        }

        return false;
    }

    private function postToSlack(Webhook $Webhook)
    {
        $data = array(
            'WEBHOOK_ID' => $Webhook->{Webhook::ID},
            "EVENT" => $Webhook->{Webhook::EVENT},
            "SOURCE" => $Webhook->{Webhook::SOURCE},
            "URL" => request()->fullUrl()
        );

        $title = sprintf("New Incoming Webhook from %s", $Webhook->{Webhook::SOURCE});

        slack($data, $title)->webhook(env('SLACK_WEBHOOK_NOTIFICATION'), null)
            ->info()
            ->post();
    }
}
