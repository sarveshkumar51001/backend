<?php
namespace App\Library\Webhook;

class SlackTranslation
{

    private $source;

    private $data;

    public function __construct($source, $data)
    {
        $this->source = $source;
        $this->data = $data;
    }

    public static function handle()
    {
        $source = $this->source;
        $data = $this->data;

        return method_exists($this, $source) ? $this->{$source}() : $data;
    }

    private function instapage()
    {
        $data = $this->data;

        $instapage_metafields = [
            'page_url',
            'pageshown',
            'variationshown',
            'desktopmobile',
            'timestamp',
            'ipaddress',
            'referralsource',
            'adcampaign',
            'page_id',
            'page_name',
            'variant',
            'ip'
        ];

        $this->data = array_except($data, $instapage_metafields); // Removing metadata fields from post data

        return $this->data;
    }
}