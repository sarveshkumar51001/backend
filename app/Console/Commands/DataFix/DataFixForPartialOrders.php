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

    const ORDERS_LIST = [
        //
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

        $this->info("---- START OF PROCESS ---");
        $order_list = DataFixForPartialOrders::ORDERS_LIST;

        foreach ($order_list as $order_id) {

            // Fetching ObjectID by order_id and applying unset and update operations
            $Object = ShopifyExcelUpload::where('order_id', $order_id)->first();
            $this->info("Processing Shopify Bulk Order " . $Object->_id);

            $Object->unset('order_id')->update(['payments.0.processed' => 'No', 'job_status' => 'pending']);

            // Calling Job class run function and passing instance of raw data.
            $Data = new DataRaw(ShopifyExcelUpload::find($Object->_id)->toArray());
            $Order = Job::run($Data);
            $order_name = $Order['name'];

            $this->info(sprintf('New Order Created %s', $order_name));

            // Cancelling orders after replacement orders created on shopify.
            $cancel_reason = sprintf("Cancel Reason: Incorrect order, new replacement order with name %s created on shopify.", $order_name);
            $ShopifyAPI = new API();
            $cancelled_order = $ShopifyAPI->CancelOrder($order_id, []);
            $ShopifyAPI->UpdateOrder($order_id, ["note" => $cancel_reason]);

            $this->info(sprintf('Order ID %s [%s] canceled order', $order_id, $cancelled_order["name"]));
            $this->comment("--------------------");
        }
        $this->info("---- END OF PROCESS ---");
        return;
    }

}

