<?php
namespace App\Library\Instapage;

use App\Models\Webhook;

class WebhookDataInstapage
{
	const INSTA_METAFIELDS= [
            'page_id',
            'page_name',
            'page_url',
            'pageshown',
            'variationshown',
            'desktopmobile',
            'timestamp',
            'ipaddress',
            'referralsource',
            'adcampaign',
            'variant',
            'ipaddress',
            'ip'
        ];

	public static function getFormData(array $data)
    {
        $data = array_except($data, self::INSTA_METAFIELDS);
        return $data;
    }
}