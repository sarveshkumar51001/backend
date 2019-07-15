<?php
namespace App\Http\Controllers\Webhooks\Shopify;

use App\Models\Product as ProductModel;
use Illuminate\Http\Request;
use App\Library\Shopify\API;
use App\Models\ShopifyExcelUpload;

class Product
{

    public function create(Request $request)
    {
        $source = explode('.', $request->header('X-Shopify-Shop-Domain', null))[0];

        $new_request = $request->all();
        $new_request['domain_store'] = $source;

        $response = ProductModel::create($new_request);

        if ($response) 
        {   return response('ok', 200); }
        else
        {   return response('error', 400); }
    }

    public function update(Request $request)
    {
        $source = explode('.', $request->header('X-Shopify-Shop-Domain', null))[0];

        $new_request = $request->all();
        $new_request['domain_store'] = $source;
        $product_id = $new_request['id'];

        $response = ProductModel::where('id', $product_id)->update($new_request);

        if ($response) 
        {   return response('ok', 200); }
        else
        {   return response('error', 400); }

    }

    public function delete(Request $request)
    {
        //
    }
}