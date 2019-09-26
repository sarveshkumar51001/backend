<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\API;
use App\Models\ShopifyExcelUpload;
use App\Models\Webhook;

class RefundCreate
{
    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_refund_data = $Webhook->body();

        self::sync_payment_details($order_refund_data);
    }

    private static function sync_payment_details($data)
    {

        #Fetch the document associated with the order id, retrieved from the refund webhook;
        $document = ShopifyExcelUpload::where('order_id', $data['order_id'])->first(['final_fee_incl_gst', 'payments']);

        $Payments = $document->payments;
        $refundable_amount = $data['transactions'][0]['amount'];
        $status = '';

        if (!empty($document)) {
            # Reverse indexing on payments;
            for ($reverse_index = count($Payments) - 1; $reverse_index >= 0; $reverse_index--) {
                $transaction_amount = $Payments[$reverse_index]['amount'];

                # Consider a payment for refund iff it is processed and the total transaction amount is not equal to the refund amount for that transaction;
                if ($Payments[$reverse_index]['processed'] == 'Yes' && $Payments[$reverse_index]['refund_amount'] != $transaction_amount) {
                    if ($transaction_amount - $refundable_amount >= $refundable_amount) {
                        $Payments[$reverse_index]['refund_amount'] += $refundable_amount;
                        $refundable_amount = 0;
                    } else {
                        $past_refund_amount = $Payments[$reverse_index]['refund_amount'];
                        $Payments[$reverse_index]['refund_amount'] += $transaction_amount - $past_refund_amount;
                        $refundable_amount = $refundable_amount - $transaction_amount + $past_refund_amount;
                    }
                }
                if ($refundable_amount == 0) {
                    break;
                }
            }
            $total_refund_amount = array_sum(array_column($Payments, 'refund_amount'));
            $status = ($total_refund_amount == $document['final_fee_incl_gst']) ? "canceled" : "partially refunded";
            ShopifyExcelUpload::where('order_id', $data['order_id'])->update(['payments' => $Payments, 'job_status' => $status,'refunded_amount'=> $total_refund_amount]);

            self::update_order_on_shopify($total_refund_amount,$data['order_id']);
        }
    }

    private static function update_order_on_shopify($amount,$order_id){

        $ShopifyAPI = new API();
        $order = $ShopifyAPI->GetOrder($order_id);

        $notes_array_packet = $order['note_attributes'];
        $notes_array_packet[] = [
            "name" => "Amount Refunded",
            "value" => $amount
        ];
        $order_details = [
            "note_attributes" => $notes_array_packet
        ];

        $ShopifyAPI->UpdateOrder($order_id, $order_details);
    }
}



