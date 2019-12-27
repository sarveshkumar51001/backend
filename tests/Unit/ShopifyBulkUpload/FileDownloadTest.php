<?php

namespace Tests\Unit\ShopifyBulkUpload;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FileDownloadTest extends TestCase

{
    public function testSampleFileDownload(){
        $path = 'public/shopify/sample_shopify_file.xls';
        $response = Response::download($path);
        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testInstituteMappingDownload(){
        $path = 'public/shopify/Delivery_Institution_Details.xlsx';
        $response = Response::download($path);
        $this->assertEquals($response->getStatusCode(), 200);

    }
}
