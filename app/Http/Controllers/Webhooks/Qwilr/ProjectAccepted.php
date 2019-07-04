<?php
namespace App\Http\Controllers\Webhooks\Qwilr;

use Illuminate\Http\Request;
use App\Models\Webhook;
use App\Library\Qwilr\Events\ProjectAccepted as QwilrProjectAccepted;
use App\Library\Qwilr\Entities\Item;

class ProjectAccepted
{

    public function handle(Request $request)
    {
        $webhook_id = $request->webhook_id;
        $Webhook = Webhook::findOrFail($request->webhook_id);
        // dd($data);
        $total = 0;
        $line_items = [];

        $ProjectAccepted = new QwilrProjectAccepted($Webhook->{Webhook::DATA});

        dd( [
            "total" => $ProjectAccepted->getTotal(),
            "quote_id" => $ProjectAccepted->getQouteId(),
            "opportunity_id" => $ProjectAccepted->getOpportunityId(),
            "checkout_url" => '',
            "qoute_pdf_url" => $ProjectAccepted->getQuoteUrl(),
            "line_items" => $ProjectAccepted->getLineItems()
        ]);
    }
}