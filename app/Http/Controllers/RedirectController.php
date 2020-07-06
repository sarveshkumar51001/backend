<?php

namespace App\Http\Controllers;


use Illuminate\Support\Str;

class RedirectController
{

    public function reynott_link()
    {
        $data = request()->all();
        $quant1 = $quant2 = 0;
        $base_url = $redirect_url = $quant2_string = $quant1_string = "";

        if (!empty($data)) {
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

            // Variant for Delivery Charges
            $variant_delivery_charges_single = 32926112448598;
            $variant_delivery_charges_multiple = 32926112481366;

            $url = "https://valedra.myshopify.com/cart/";

            if ($quant1 == 0 && $quant2 == 0) {
                $redirect_url = "";
            } else {
                if ($quant1 >0 && $quant2 >0){
                    $base_url = $url.$variant_test_book.':'.$quant1.','.$variant_test.':'.$quant2;
                } else {
                    if ($quant1 > 0) {
                        $base_url = $url . $variant_test_book . ':' . $quant1;
                    }elseif ($quant2 > 0) {
                        $base_url = $url . $variant_test . ':' . $quant2;
                    }
                }

                if(!empty($data['deliver_books_to_residential_address']) && strtolower($data['deliver_books_to_residential_address']) == 'yes') {
                    if($quant1 == 1) {
                        $base_url .= ",$variant_delivery_charges_single:1";
                    } elseif ($quant1 > 1) {
                        $base_url .= ",$variant_delivery_charges_multiple:1";
                    }
                }

            $query_data = [
                'checkout[email]' => $data['email'] ?? '',
                'checkout[shipping_address][first_name]' => $data['parent_name-first'] ?? '',
                'checkout[shipping_address][last_name]' => $data['parent_name-last'] ?? '',
                'checkout[shipping_address][address1]' => $data['address-address'] ?? '',
                'checkout[shipping_address][city]' => $data['address-city'] ?? '',
                'checkout[shipping_address][state]' => $data['address-state'] ?? '',
                'checkout[shipping_address][zip]' => $data['address-zip'] ?? '',
                'checkout[shipping_address][phone]' => $data['mobile_number'] ?? ''
            ];

            $query_params = '?'.http_build_query($query_data);
            $redirect_url = $base_url.$query_params;
        }
    }

        return view('shopify-checkout-redirect')->with('url',$redirect_url);
    }
}
