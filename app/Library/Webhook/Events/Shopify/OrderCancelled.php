<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\API;
use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use App\Models\Webhook;

class OrderCancelled
{
    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_cancel_data = $Webhook->body();

        self::mark_order_cancelled($order_cancel_data);
    }

    private static function mark_order_cancelled($data)
    {
        // Fetch the document associated with the order id, retrieved from the refund webhook;
        $document = ShopifyExcelUpload::where('order_id', $data['id'])->firstOrFail();

        $Payments = $document->payments;
        $refunds = $data['refunds'] ?? [];
        $refund_transactions = $refunds['transactions'] ?? [];

        // If no refund was initiated then dropout student
        if(empty($refund_transactions)) {
            // Parsing payments in reverse order
            for ($reverse_index = count($Payments) - 1; $reverse_index >= 0; $reverse_index--) {
                $Payment = new Payment($Payments[$reverse_index], $reverse_index);

                // Skipping Processed Payments
                if ($Payment->isProcessed()) {
                    continue;
                }

                // Cancelling unprocessed payment
                $Payments[$reverse_index][ShopifyExcelUpload::PaymentIsCanceled] = true;
            }

            $document->update([
                'is_canceled' => true,
                'payments' => $Payments,
                'job_status' => "dropout",
            ]);
        }
    }
}



