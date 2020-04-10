<?php
namespace App\Library\Instapage;
use App\Models\Webhook;
use Illuminate\Support\Arr;

class WebhookDataInstapage
{

    const INSTA_METAFIELDS = [
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

    const Excel = "excel";
    const View = "view";

    public static function getFormData(array $data)
    {
        $data = array_keys(Arr::except($data, self::INSTA_METAFIELDS));
        return $data;
    }

    public static function getInstaPageList($start, $end, $page_id, $type)
    {
         $q = Webhook::where('data.body.page_id', (int) $page_id)
            ->whereBetween('created_at', [$start, $end]);
         if($type == WebhookDataInstapage::Excel) {
             $Leads = $q->orderBy('_id','desc')->get();
         }
         else {
             $Leads = $q->orderBy('_id','desc')->paginate(100);
         }
         return $Leads;
    }
}



