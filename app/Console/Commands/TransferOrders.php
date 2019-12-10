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
    protected $signature = 'TransferOrders:U2U';

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
        // Ask user to input the file id and fetch the file data..
        $file_id = $this->ask('Please enter the file id');
        $file_data = Upload::where('file_id',$file_id)->first();

        // Fetch user who uploaded the file..
        $User = User::where('_id',$file_data->user_id)->first();
        $this->info(sprintf('This file was uploaded by %s',$User->name));

        // Ask user to enter the email id to whom the transfer is to be done....
        $to_email = $this->ask('Please enter the email id of the user to whom you want to transfer the file');

        //Raise error if 'to' and 'from' email is found to be same....
        if($to_email == $User->email){
            $this->error('File cannot be transferred to the user who created it. Please enter the correct email');
            return ;
        }

        // Fetching completed orders count for that file and matching with the total orders of that file and if matched
        // then proceed further otherwise throw an error.
        $orders_count = ShopifyExcelUpload::where('file_id',$file_id)->where('job_status','completed')->count();
        if($orders_count != $file_data->metadata['new_order']){
            $this->error('File cannot be transferred as some of the orders are still due for completion.');
            return ;
        }

        $this->info(sprintf('Total number of entries to be affected are %s',$file_data->metadata['new_order']));

        // Asking user to choose whether to continue or not....
        $default_choice = 1;
        $choice = $this->choice('Do you wish to continue?', ['Yes', 'No'], $default_choice);

        if($choice == 'No'){
            return;
        }
        $ToUser = User::where('email',$to_email)->first();

        // Return error if no user found with the email address provided else transfer the file to the new owner.
        if(empty($ToUser)){
            $this->error('Email address provided does not exists. Please check and try again.');
            return ;
        }else {
            // Set a field owner and update it with the id of the user to whom the orders has to be transferred.
            ShopifyExcelUpload::where('file_id', $file_id)->update(['owner' => $ToUser->_id]);
        }
        $this->info(sprintf('File transferred successfully. New owner of the file is %s.',$ToUser->name));

        // Fetching orders list by file id....
        $orders_list = ShopifyExcelUpload::where('file_id',$file_id)->get(['order_id'])->toArray();

        // Fetch tags for the order change the owner email address and update order with tags....
        foreach($orders_list as $order){
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
        }
        $this->info("Transfer of orders and update in shopify done successfully.");

       return ;
    }
}
