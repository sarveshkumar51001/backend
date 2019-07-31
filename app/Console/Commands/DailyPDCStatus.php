<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Shopify\DB;
use App\Models\ShopifyExcelUpload;

class DailyPDCStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PDCStatus:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sending daily PDC status notifications on Slack';

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
        $doc = DB::get_all_post_dated_payments();
    }
}
