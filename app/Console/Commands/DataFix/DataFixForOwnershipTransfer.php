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

        $users = User::all(['_id'])->toArray();
        $user_list = Arr::flatten(array_values($users));

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach($user_list as $user){
            $bar->advance();
            logger('yes');
            if($this->option('run')){
                break;
            }
            ShopifyExcelUpload::where("uploaded_by", $user)->update(["owner" => $user]);
        }
        $this->info("-----PROCESS COMPLETED-----");
        return ;
    }













}
