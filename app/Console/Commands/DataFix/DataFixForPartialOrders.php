<?php
namespace App\Console\Commands\DataFix;

use App\Jobs\ShopifyOrderCreation;
use App\Library\Shopify\API;
use App\Library\Shopify\DataRaw;
use App\Library\Shopify\Job;
use Illuminate\Console\Command;
use App\Library\Shopify\DB;
use App\Models\ShopifyExcelUpload;

class DataFixForPartialOrders extends Command
{

    const ORDERS_LIST = [1339071856674,
        1339072610338,
        1339079196706,
        1339079753762,
        1339088568354,
        1339092434978,
        1339093123106,
        1339095416866,
        1339112456226,
        1339112816674,
        1339113340962,
        1339113504802,
        1339113734178,
        1339114160162,
        1339114782754,
        1339115012130,
        1339115241506,
        1339115470882,
        1339115733026,
        1339115995170,
        1339116159010,
        1339116716066,
        1339116879906,
        1339116912674,
        1339117109282,
        1339117207586,
        1339117404194,
        1339117502498,
        1339117764642,
        1339117961250,
        1339118125090,
        1339118288930,
        1339118485538,
        1339119206434,
        1339119927330,
        1339120025634,
        1339138441250,
        1339138474018,
        1339138637858,
        1351923138594,
        1351924449314,
        1351924842530,
        1351942012962,
        1352070889506,
        1352071479330
    ];
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
     * @throws \Exception
     */
    public function handle()
    {

        $order_list = DataFixForPartialOrders::ORDERS_LIST;
        $order_name = "";

        foreach($order_list as $order_id){

            // Fetching ObjectID by order_id and applying unset and update operations
            $Object = ShopifyExcelUpload::where('order_id',$order_id)->first();
            $Object->unset('order_id')->update([ 'payments.0.processed'=> 'No', 'job_status' => 'pending']);

            // Calling Job class run function and passing instance of raw data.
            $Data = new DataRaw(ShopifyExcelUpload::find($Object->_id)->toArray());
            $Order = Job::run($Data);
            $order_name = $Order['name'];

            $this->info(sprintf('Created order with order name %s',$order_name));

            // Cancelling orders after replacement orders created on shopify.
            $cancel_reason = sprintf("Cancel Reason: Incorrect order, new replacement order with name %s created on shopify.",$order_name);
            $ShopifyAPI = new API();
            $ShopifyAPI->CancelOrder($order_id, []);
            $ShopifyAPI->UpdateOrder($order_id,["note"=>$cancel_reason]);

            $this->info(sprintf('Canceled order with order id %s',$order_id));

        }
        $this->info("All false orders cancelled and replacement orders created successfully");
        return ;
    }

}

