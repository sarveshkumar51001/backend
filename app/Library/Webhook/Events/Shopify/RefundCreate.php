<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Models\Webhook;

class RefundCreate
{
    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_refund_data = $Webhook->body();
        logger(json_encode($order_refund_data));

        self::sync_payment_details($order_refund_data);
    }

    private static function sync_payment_details($data)
    {

        #Fetch the document associated with the order id, retrieved from refund webhook.
        $document = ShopifyExcelUpload::where('order_id', $data['order_id'])->first(['final_fee_incl_gst', 'payments']);

        $Payments = $document->payments;
        $captured_refund_amount = $data['transactions'][0]['amount'];
        $status = '';

        if (!empty($document)) {
            # Reverse index for payments
            for ($reverse_index = count($Payments) - 1; $reverse_index >= 0; $reverse_index--) {
                $transaction_amount = $Payments[$reverse_index]['amount'];

                if ($Payments[$reverse_index]['processed'] == 'Yes' && $Payments[$reverse_index]['refund_amount'] != $transaction_amount) {
                    if ($transaction_amount >= $captured_refund_amount) {
                        $Payments[$reverse_index]['refund_amount'] += $captured_refund_amount;
                        $refund = 0;
                    } else {
                        $past_refund_amount = $Payments[$reverse_index]['refund_amount'];
                        $Payments[$reverse_index]['refund_amount'] += $transaction_amount - $past_refund_amount;
                        $refund = $captured_refund_amount - $transaction_amount + $past_refund_amount;
                    }
                }
                if ($captured_refund_amount == 0) {
                    break;
                }
            }
            $total_refund_amount = array_sum(array_column($Payments, 'refund_amount'));
            $status = ($total_refund_amount == $document['final_fee_incl_gst']) ? "canceled" : "partially refunded";
            ShopifyExcelUpload::where('order_id', $data['order_id'])->update(['payments' => $Payments, 'job_status' => $status]);
        }
    }
}



