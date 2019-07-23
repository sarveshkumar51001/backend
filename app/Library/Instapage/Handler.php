<?php
namespace App\Library\Instapage;

class Handler
{
	const METAFIELDS= [
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
        // Removing metadata fields from post data
        $data = array_except($data, self::METAFIELDS);

        return $data;
    }
    
}