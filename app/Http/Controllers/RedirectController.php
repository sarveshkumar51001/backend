<?php

namespace App\Http\Controllers;


use Illuminate\Support\Str;

class RedirectController
{

    public function reynott_link()
    {
        $data = request()->all();
        $quant1 = $quant2 = 0;
        $redirect_url = "";

        if(!empty($data)) {
            foreach (array_values($data) as $value) {
                if (Str::contains($value, 'Book & Tests')) {
                    $quant1 += 1;
                } elseif (Str::contains($value, 'Only Test')) {
                    $quant2 += 1;
                }
            }

            // Variant Ids for book & tests and test variant
            $variant_test_book = 32809609101398;
            $variant_test = 32809609134166;

            $base_url = "https://valedra.myshopify.com/cart/" . $variant_test_book . ':' . $quant1 . ',' . $variant_test . ':' . $quant2;
            $param1 = "?checkout[email]=" . $data['email'] . '&checkout[shipping_address][first_name]=' . $data['fathers_name-first'];
            $param2 = "&checkout[shipping_address][last_name]=" . $data['fathers_name-last'] . "&checkout[shipping_address][address1]=" . $data['address-address'];
            $param3 = "&checkout[shipping_address][city]=" . $data['address-city'] . "&checkout[shipping_address][state]=" . $data['address-state'] . "&checkout[shipping_address][zip]=" . $data['address-zip'];

            $redirect_url = $base_url . $param1 . $param2 . $param3;
        }

        return view('shopify-checkout-redirect')->with('url',$redirect_url);
    }
}
