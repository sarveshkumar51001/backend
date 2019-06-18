<?php

namespace App\Jobs;

use App\Library\Shopify\Job;
use App\Library\Shopify\DB;
use App\Library\Shopify\DataRaw;

use App\Models\ShopifyExcelUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ShopifyOrderCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

	/**
	 * ShopifyOrderCreation constructor.
	 *
	 * @param ShopifyExcelUpload $data
	 */
    public function __construct(ShopifyExcelUpload $data) {
        $this->data = $data;
    }

    public function handle() {
	    $Data = new DataRaw($this->data->toArray(), $this);

	    try {
            $Job = $this->job;
            
	    	Job::run($Data,$Job);
        } catch(\Exception $e) {
            if (app()->bound('sentry')) { app('sentry')->captureException($e); }
        	DB::mark_status_failed($Data->ID(), [
        		'message' => $e->getMessage(),
		        'time' => time(),
		        'job_id' => $this->job->getJobId()
	        ]);

        	// Posting to slack if job fails
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_URL,env('SLACK_WEBHOOK', null));
        	curl_setopt($ch, CURLOPT_POST, 1);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, self::GetPayload($e, $this->job->getJobId(), json_encode($this->data->toArray())));
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_exec($ch);
        	curl_close($ch);
        	
        	// Marking Job as Failed
            $this->fail($e);
        }
    }
    
    protected  static function GetPayload(\Exception $exception, $JobId, $JobData) {
        $attachments = $data = [];
        
        $attachments['title'] = 'Job failed with ID: ' . $JobId;
        $attachments['color'] = '#FFAE42';
        
        $message = "Reason of failure: " . $exception->getMessage() . "\n";
        $attachments['text'] = $message;
        
        $data['time'] = date("d-m-Y h:i:s a", time());
        $data['env'] = env('APP_URL');
        $data['exception_class'] = get_class($exception);
        //$data['data'] = $JobData;
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = [
                'title' => $key,
                'value' => $value,
                'short' => true
            ];
        }
        
        $attachments['fields'] = $fields;
        
        $payload['attachments'][] = $attachments;
        
        return json_encode($payload);
    }
}