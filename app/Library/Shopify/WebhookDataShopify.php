<?php
namespace App\Library\Shopify;

use App\Models\Webhook;

class WebhookDataShopify
{
    const SHOPIFY_METAFIELDS= [
            'body_html',
            'template_suffix',
            'admin_graphql_api_id',
            'variants',
            'options',
            'images',
            'image'
        ];

	public static function getFormData(array $data)
    {
        $data = array_except($data, self::SHOPIFY_METAFIELDS);
        return $data;
    }
}