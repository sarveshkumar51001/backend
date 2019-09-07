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
    public function __construct(ShopifyExcelUpload $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $Data = new DataRaw($this->data->toArray(), $this);

        try {
            $Job_ID = $this->job->getJobId();

            Job::run($Data);
        } catch (\PHPShopify\Exception\ResourceRateLimitException $e) {
            $this->release(2);
        } catch (\Exception $e) {
            DB::mark_status_failed($Data->ID(), [
                'message' => $e->getMessage(),
                'time' => time(),
                'job_id' => $Job_ID
            ]);

            // Posting to slack and sentry if job fails
            slack($e)->post();
            // Marking Job as Failed
            $this->fail($e);
        }
    }
}
