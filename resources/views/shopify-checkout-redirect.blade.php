<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="row justify-content-center" style="top: 40%;left:30%;position: absolute">
        <div class="col">
            @if(!empty($url))
                <h4 class="alert-success"><b>Redirecting you to the Shopify Checkout page</b></h4>
                <meta http-equiv = "refresh" content = "1; url = {{$url}}" />
            @else
                <h4 class="alert-danger"><b>Please select at least one subject in order to purchase the olympiad.</b></h4>
            @endif
        </div>
    </div>
</body>
</html>


