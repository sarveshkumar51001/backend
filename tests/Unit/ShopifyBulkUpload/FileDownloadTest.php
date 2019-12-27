<?php

namespace Tests\Unit\ShopifyBulkUpload;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FileDownloadTest extends TestCase

{
    public function testSampleFileDownload(){
        $response = Response::download('public/shopify/sample_shopify_file.xls');
        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testInstituteMappingDownload(){
        $response = Response::download('public/shopify/Delivery_Institution_Details.xlsx');
        $this->assertEquals($response->getStatusCode(), 200);

    }
}
