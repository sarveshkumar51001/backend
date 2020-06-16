<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="row justify-content-center mt-5 align-self-center">
        <img src="{{asset("img/logo.png")}}" />
    </div>
    <div class="row justify-content-center mt-4">
        <div class="box" style="display: flex;align-items: center">

        <div class="col">
        @if(!empty($url))
            <h4 class="alert-success"><b>Redirecting you to the Checkout page...</b></h4>
                <meta http-equiv="refresh" content="0; url = {{$url}}" />
        @else
            <h4 class="alert-danger"><b>Please select at least one subject in order to purchase the olympiad.</b></h4>
            <h5 class="alert-info" style="text-align: center">Redirecting you back to the form...</h5>
            <h6 style="text-align: center">If not redirected <a href="https://apeejay.formstack.com/forms/reynott_orange_olympiad">Click Here</a></h6>
            <script type="application/javascript">
                setTimeout(function() { history.back(); }, 3000);
            </script>
        @endif
        </div>
        </div>
    </div>
</body>
</html>


