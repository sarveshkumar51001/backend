<?php

namespace App\Console\Commands\DataFix;

use App\Jobs\ShopifyOrderCreation;
use App\Library\Shopify\API;
use App\Library\Shopify\DataRaw;
use App\Library\Shopify\Job;
use App\User;
use Illuminate\Console\Command;
use App\Library\Shopify\DB;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Helper\ProgressBar;


/**
 * @codeCoverageIgnore
 * Class DataFixForOwnershipTransfer
 * @package App\Console\Commands\DataFix
 */
class DataFixForOwnershipTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DataFix:TransferOwnership {--run=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for adding owner field for previously created orders.';

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

        $ShopifyExcelUpload = ShopifyExcelUpload::query();
        $collection = $ShopifyExcelUpload->where('owner','exists',false)->get(['_id','uploaded_by'])->toArray();

        $bar = $this->output->createProgressBar(count($collection));
        $bar->start();

        foreach($collection as $document) {
            $ShopifyExcelUpload->where('_id', $document['_id'])->update(['owner'=>$document['uploaded_by']]);
            $bar->advance();
            if ($this->option('run')) {
                $bar->finish();
                $order_name = (isset($document['shopify_order_name']) ? $document['shopify_order_name'] : '');
                $this->info(sprintf("Single order with order name %s transferred successfully.",$order_name));
                break;
            }
        }
        $this->info("-----PROCESS COMPLETED-----");
        return ;
    }













}
