<?php
namespace App\Console\Commands\DataFix;

use App\Jobs\ShopifyOrderCreation;
use Illuminate\Console\Command;
use App\Library\Shopify\DB;
use App\Models\ShopifyExcelUpload;

class DataFixForPartialOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DataFix:BA-107';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for fixing falsely created partial orders on shopify.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order_list = ShopifyExcelUpload::ORDERS_LIST;
        $ObjectIDList = [];

        foreach($order_list as $order_id){

            // Find document id by order id and store it in seperate list for job dispatching
            $ObjectID = ShopifyExcelUpload::where('order_id',$order_id)->get(['_id'])->first()->toArray()['_id'];
            $ObjectIDList[] = $ObjectID;

            // Update and unset fields for reprocessing of false created orders
            ShopifyExcelUpload::where('order_id',$order_id)->update([ 'payments.0.processed'=> 'No', 'job_status' => 'pending']);
            ShopifyExcelUpload::where('order_id',$order_id)->unset('order_id');
        }
        // Dispatching jobs on queue
        foreach (ShopifyExcelUpload::findMany($ObjectIDList) as $Object) {
            ShopifyOrderCreation::dispatch($Object)->onQueue('low');
        }
        return ;
    }

}

