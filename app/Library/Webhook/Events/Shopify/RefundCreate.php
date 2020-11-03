<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\API;
use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use App\Models\Webhook;
use Carbon\Carbon;

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

        // Fetch the document associated with the order id, retrieved from the refund webhook;
        $document = ShopifyExcelUpload::where('order_id', $data['order_id'])->firstOrFail();

        $Payments = $document->payments;
        $refund_transactions = $data['transactions'] ?? [];
        $order_note = [];
        $has_refund = false;
        $total_received_payment = 0;
        foreach ($refund_transactions as $refund_transaction) {
            $transaction_refund_amount = $refund_transaction['amount'] ?? 0;
            $transaction_refund_date = Carbon::parse($refund_transaction['processed_at'])->timestamp ?? now()->timestamp;

            if($transaction_refund_amount == 0) {
                continue;
            }

            $has_refund = true;

            $order_note = [
                "type" => "Refund",
                "reason" => $data['note'] ?? '',
                "amount" => $transaction_refund_amount,
                "added_on" => !empty($data['processed_at']) ? Carbon::parse($data['processed_at'])->timestamp : ''
            ];

            // Parsing payments in reverse order
            for ($reverse_index = count($Payments) - 1; $reverse_index >= 0; $reverse_index--) {
                $Payment = new Payment($Payments[$reverse_index], $reverse_index);

                // Skipping Unprocessed Payments
                if(! $Payment->isProcessed()) {
                    continue;
                }

                // Skipping Refunded Payment
                if($Payment->isRefunded()) {
                    continue;
                }

                $amount_available_to_refund = $Payment->AvailableToRefund();
                $total_received_payment += $amount_available_to_refund;

                if(empty($Payments[$reverse_index][ShopifyExcelUpload::PaymentRefundAmount])) {
                    $Payments[$reverse_index][ShopifyExcelUpload::PaymentRefundAmount] = 0;
                }

                // If amount available to refund is less than refund transaction amount then adjust amount
                if($amount_available_to_refund < $transaction_refund_amount) {
                    $Payments[$reverse_index][ShopifyExcelUpload::PaymentRefundAmount] += $Payment->getAmount();
                    $transaction_refund_amount -= $Payment->getAmount();
                } else {
                    $Payments[$reverse_index][ShopifyExcelUpload::PaymentRefundAmount] += $transaction_refund_amount;
                    $transaction_refund_amount = 0;
                }

                $Payments[$reverse_index][ShopifyExcelUpload::PaymentRefundDate] = $transaction_refund_date;

                // Exit loop when all refund amount has been consumed
                if($transaction_refund_amount == 0) {
                    break;
                }
            }
        }

        if($has_refund) {
            $total_refunded_amount = array_sum(array_column($Payments, ShopifyExcelUpload::PaymentRefundAmount));
            $status = ($total_refunded_amount == $document['final_fee_incl_gst']) ? "full refund" : "partial refund";

            if($total_refunded_amount > $total_received_payment) {
                throw new Exception("Refund amount is greater than received amount");
            }

            // Updating Refund Reason
            $order_notes = $document['order_notes'] ?? [];
            $order_notes[] = $order_note;

            $document->update([
                'order_notes' => array_filter($order_notes),
                'payments' => $Payments,
                'job_status' => $status,
                'refunded_amount'=> $total_refunded_amount
            ]);
        }
    }
}



