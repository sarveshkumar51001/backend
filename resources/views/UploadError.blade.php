<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Shopify- Valedra</title>
    <link href="{{ URL::asset('vendors/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/simple-line-icons.min.css') }} " rel="stylesheet">
    <link href="{{ URL::asset('css/style.css') }}" rel="stylesheet">
</head>

<body class="app flex-row align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h4 class="pt-3">Duplicate entry found in the excel sheet</h4>
                <p class="text-muted">The data you are trying to upload is already present in the system.</p>

                <p class ="text-info">Redirect to the bulk upload page by clicking this button.</p>
                <button type="button" onclick="window.location='{{ URL::route('bulkupload.ShopifyBulkUpload') }}'">Shopify Bulk Upload</button>
            </div>
            </div>
        </div>
    </div>
</body>
</html>