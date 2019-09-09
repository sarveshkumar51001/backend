<?php
namespace App\Library\Webhook\Events\Classpro;

use function slack;
use GuzzleHttp\Client;
use App\Models\Webhook;
use App\Library\Webhook\Channel;
use App\Library\Classpro\WebhookDataClasspro;

class LeadCreate
{
	public static function handle(Webhook $Webhook) {
        self::postWebEnquiry($Webhook);
        self::postToSlack($Webhook);
    }

    private static function postWebEnquiry(Webhook $Webhook) {

        $client = new Client();
        $url = 'https://www.classpro.in/api/v3/web_enquiries';

        $payload = array(
                        "token" => env('CLASSPRO_TOKEN'),
                        "web_enquiry" => array(
                            "firstname" => "Jain",
                            "lastname" => "Test",
                            "contact_no" => "9628578481",
                            "branch_id" => "8832",
                            "website_course_id" => 3356
                        ));

        $request = $client->post($url,  ['form_params'=>$payload]);
    }

    private static function postToSlack(Webhook $Webhook) {

        $data = WebhookDataClasspro::getFormData($Webhook->body());
        $page_id = $Webhook->body()['page_id'];
        $title = sprintf(":tada: New Lead Captured (H&R) - ", $Webhook->body()['page_name']);
        
        $channel = Channel::SlackUrl($page_id);

        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}