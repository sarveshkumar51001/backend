<?php
namespace App\Library\Shopify;

use App\Models\Webhook;

class WebhookDataShopify
{
    const SHOPIFY_PRODUCT_METAFIELDS= [
            'id',
            'title',
            'admin_graphql_api_id',
            'published_scope',
            'template_suffix',
            'published_at',
            'created_at',
            'updated_at',
            'body_html',
            'variants',
            'options',
            'images',
            'image',
            'handle'
        ];

    const SHOPIFY_ORDER_METAFIELDS= [
            'id',
            'email',
            'closed_at',
            'created_at',
            'updated_at',
            'number',
            'note',
            'token',
            'gateway',
            'test',
            'subtotal_price',
            'total_weight',
            'total_tax',
            'taxes_included',
            'currency',
            'financial_status',
            'confirmed',
            'total_discounts',
            'total_line_items_price',
            'cart_token',
            'buyer_accepts_marketing',
            'name',
            'referring_site',
            'landing_site',
            'cancelled_at',
            'cancel_reason',
            'checkout_token',
            'reference',
            'user_id',
            'location_id',
            'source_identifier',
            'source_url',
            'processed_at',
            'device_id',
            'phone',
            'customer_locale',
            'app_id',
            'browser_ip',
            'landing_site_ref',
            'order_number',
            'discount_applications',
            'discount_codes',
            'note_attributes',
            'payment_gateway_names',
            'processing_method',
            'checkout_id',
            'source_name',
            'tax_lines',
            'tags',
            'contact_email',
            'order_status_url',
            'presentment_currency',
            'total_line_items_price_set',
            'total_discounts_set',
            'total_shipping_price_set',
            'subtotal_price_set',
            'total_price_set',
            'total_tax_set',
            'total_tip_received',
            'admin_graphql_api_id',
            'shipping_lines',
            'billing_address',
            'shipping_address',
            'fulfillments',
            'refunds'
        ];    

	public static function getFormData(array $data)
    {
        if ($data){
            $data = array_except($data, self::SHOPIFY_PRODUCT_METAFIELDS);
        }
        elseif ($data) {
            $data = array_except($data, self::SHOPIFY_ORDER_METAFIELDS);
        }
        return $data;
    }
}