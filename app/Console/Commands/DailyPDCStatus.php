<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Shopify\DB;
use App\Models\ShopifyExcelUpload;
use function slack;
use Carbon\Carbon;

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
    protected $description = 'Command for sending daily Post Dated Collection status notifications on Slack';

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
        // Fetching all post dated payments from the database
        $post_dated_payments = DB::get_all_post_dated_payments();

        $today_data = [];
        $yesterday_data = [];
        $today_count = [];
        $yesterday_count = [];
        
        // Looping through all payments
        foreach( $post_dated_payments as $payments){

            $school_name = $payments['school_name'];
            $payment_array = $payments['payments'];

            // Getting keys of the payments which are due to be collected
            $keys = array_keys(array_column($payment_array, 'is_pdc_payment'), true);

            foreach($keys as $index => $key){

                $amount = $payment_array[$key]['amount'];
                $date = $payment_array[$key]['chequedd_date'];

                if($date == date('d/m/Y')){

                    if(!array_key_exists($school_name, $today_data)){
                        $today_data[$school_name] = (int) $amount;
                        $today_count[$school_name] = 1;
                    } else {
                        $today_data[$school_name] += $amount;
                        $today_count[$school_name] += 1;
                    }
                }

                if(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT,$date)->timestamp < time()){
                    if(!array_key_exists($school_name, $yesterday_data)){
                        $yesterday_data[$school_name] = (int) $amount;
                        $yesterday_count[$school_name] = 1;
                    } else {
                        $yesterday_data[$school_name] += $amount;
                        $yesterday_count[$school_name] += 1;
                    }
                }
            }
        }

        if(empty($yesterday_data)){
            $yesterday_collection_success_message = ":tada: All payments due for collection till yesterday have been collected.";
            slack()->title($yesterday_collection_success_message)->webhook(env('SLACK_COLLECTION_URL'))->success()->post();
        }
        else{
            $yesterday_collection_message = "Following are the number of payments along with amount school wise, that should have been collected by yesterday.";
            $yesterday_collection_data = self::merge_amount_and_count($yesterday_data,$yesterday_count);
            slack($yesterday_collection_data, $yesterday_collection_message)->webhook(env('SLACK_COLLECTION_URL'))->warn()->post();
        }

        if(empty($today_data)){
            $today_collection_success_message = ":tada: There are no payments to be collected for today.";
            slack()->title($today_collection_success_message)->webhook(env('SLACK_COLLECTION_URL'))->success()->post();
        }
        else{
            $today_collection_message = "Following are the number of payments along with amount school wise,that are to be collected by today.";
            $today_collection_data = self::merge_amount_and_count($today_data,$today_count);
            slack($today_collection_data, $today_collection_message)->webhook(env('SLACK_COLLECTION_URL'))->warn()->post();
        }
    }

    private static function merge_amount_and_count(array $amount_array, array $count_array){

        $data = [];
        foreach($count_array as $school => $count){

            $data[$school] = "₹ ".money_format("%.0n",floor($amount_array[$school])).' ('.$count.')';
        }
        return $data;
    }
}
