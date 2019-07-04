<?php
namespace App\Library\Qwilr\Events;

use App\Library\Qwilr\Entities\Item;

class ProjectAccepted
{

    private $data;

    public function getTotal()
    {
        return $this->data['acceptedTotalWithTaxes'];
    }

    public function getQouteId($item)
    {
        return $this->data['acceptanceData']['linkSecret'];
    }

    public function getLineItems()
    {
        $line_items = [];
        foreach ($this->data['acceptanceData']['configuredQuotes'] as $configuredQuote) {

            foreach ($configuredQuote['sections'] as $section) {

                $items = collect($section['items'])->where('type', 'fixedCost');

                foreach ($items as $item) {

                    if ($line_item = Item::getLineItem($item)) {

                        $line_items[] = $line_item;
                    }
                }
            }
        }
        return $line_items;
    }

    public function getAcceptors()
    {
        $acceptors = [];

        foreach ($this->data['accepters'] as $acceptor) {
            $acceptors[] = [
                'name' => $acceptor['name'],
                'email' => $acceptor['email']
            ];
        }
        
        return $acceptors;
    }

    public function getQuoteUrl()
    {
        return $this->data['acceptanceData']['backupPdf'];
    }

    public function getOpportunityId()
    {
        return 123123213;
    }
}