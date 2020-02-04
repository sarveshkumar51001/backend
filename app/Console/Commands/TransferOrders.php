<?php

namespace App\Console\Commands;

use App\Library\Shopify\API;
use App\Models\ShopifyExcelUpload;
use App\Models\Upload;
use App\User;
use Illuminate\Console\Command;

class TransferOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TransferOrders:U2U {--run=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for transferring orders from one user to another and update the same on shopify.';

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
        $file_data = [];
        $file_id = $order_name = $order_id = "";



        // Checking if the user wants to transfer a file or a single order and fetching owner accordingly
        if(empty($this->option('run'))) {
            [$User,$file_id,$file_data] = $this->userWhoOwnsFile();
        } else {
            [$User,$order_name,$order_id] = $this->userWhoOwnsOrder();
        }
        // Ask user to enter the email id to whom the transfer is to be done....
        $to_email = $this->ask('Please enter the email id of the user to whom you want to transfer.');

        //Raise error if 'to' and 'from' email is found to be same....
        if($to_email == $User->email){
            $this->error('Transfer cannot take place to the user who owns the file/order. Please enter the correct email');
            return ;
        }
        // Check if the email id provided for transfer exists or not....
        $ToUser = User::where('email',$to_email)->first();
        if(empty($ToUser)){
            $this->error('Email address provided does not exists. Please check and try again.');
            return ;
        }
        // If order name exists i.e. single order has to be transferred else complete file is to be transferred
        if($order_name){
            ShopifyExcelUpload::where('shopify_order_name',$order_name)->update(['owner' => $ToUser->_id]);
            $orders[]['order_id'] = $order_id;
        } else {
            $this->transferFileToUser($file_id,$file_data,$ToUser);
            $orders = ShopifyExcelUpload::where('file_id',$file_id)->get(['order_id'])->toArray();
        }
        // Updating tags with new owner for each order in the file.
        $this->updateTags($orders,$ToUser);

        $this->info("Transfer of order/file and update in shopify done successfully.");

       return ;
    }

    /**
     * @return mixed
     */
    private function userWhoOwnsOrder(){
        // Ask user to input the order name of the order whose ownership needs to be transferred.
        $order_name = $this->ask('Please enter the shopify order name');
        $order = ShopifyExcelUpload::where('shopify_order_name',$order_name)->first(['owner','order_id']);

        if(empty($order)){
            $this->error("The order name doesn't exists.");
            return [];
        }
        return [User::where('_id',$order['owner'])->first(),$order_name,$order['order_id']];
    }

    /**
     * @return mixed
     */
    private function userWhoOwnsFile()
    {
        // Ask user to input the file id and fetch the file data..
        $file_id = $this->ask('Please enter the file id');
        $file_data = Upload::where('file_id', $file_id)->first();

        if(empty($file_data)){
            $this->error("The file provided does'not exists.");
            return [];
        }
        // Fetch user who uploaded the file..
        $User = User::where('_id', $file_data->user_id)->first();
        $this->info(sprintf('This file was uploaded by %s', $User->name));
        return [$User,$file_id,$file_data];
    }

    private function transferFileToUser($file_id,$file_data,$ToUser)
    {
        // Fetching completed orders count for that file and matching with the total orders of that file and if matched
        // then proceed further otherwise throw an error.
        $orders_count = ShopifyExcelUpload::where('file_id', $file_id)->where('job_status', 'completed')->count();
        if ($orders_count != $file_data->metadata['new_order']) {
            $this->error('File cannot be transferred as some of the orders are still due for completion.');
            return;
        }
        $this->alert(sprintf('Total number of entries to be affected are %s', $file_data->metadata['new_order']));
        // Asking user to choose whether to continue or not....
        $default_choice = 1;
        $choice = $this->choice('Do you wish to continue?', ['Yes', 'No'], $default_choice);

        if ($choice == 'No') {
            return;
        } else {
            // Set a field owner and update it with the id of the user to whom the orders has to be transferred.
            ShopifyExcelUpload::where('file_id', $file_id)->update(['owner' => $ToUser->_id]);
        }
        $this->info(sprintf('File transferred successfully. New owner of the file is %s.', $ToUser->name));

    }

    private function updateTags($orders,$ToUser){

        // Progress bar creation and start
        $bar = $this->output->createProgressBar(count($orders));
        $bar->start();

        // Fetch tags for the order, change the owner email address and update order with tags....
        foreach($orders as $order){
            $order_id = $order['order_id'];
            $shopifyAPI = new API();
            $params = ['fields' => 'tags'];
            $tags = $shopifyAPI->GetOrder($order_id,$params)['tags'];
            $tag_array = explode(',',$tags);

            foreach($tag_array as $key => $value){
                if(\Illuminate\Support\Str::contains($value,ShopifyExcelUpload::ORG_DOMAIN)){
                    $tag_array[$key] = $ToUser->email;
                }
            }
            $shopifyAPI->UpdateOrder($order_id,['tags' => implode(',',$tag_array)]);
            $bar->advance();
        }
    }
}
