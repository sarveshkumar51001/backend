<?php

namespace Tests\Unit\ShopifyBulkUpload;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FileDownloadTest extends TestCase

{
    /**
     * Purpose: To check whether the sample excel file gets downloaded or not
     * I/P: File path to check
     * O/P: Test case should assert Equal if the response code comes out to be 200.
     */
    public function testSampleFileDownload(){
        $path = 'public/shopify/sample_shopify_file.xls';
        $response = Response::download($path);
        $this->assertEquals($response->getStatusCode(), 200);
    }

    /**
     * Purpose: To check whether the institute mapping file gets downloaded or not
     * I/P: File path to check
     * O/P: Test case should assert Equal if the response code comes out to be 200.
     */
    public function testInstituteMappingDownload(){
        $path = 'public/shopify/Delivery_Institution_Details.xlsx';
        $response = Response::download($path);
        $this->assertEquals($response->getStatusCode(), 200);

    }
}
