{{--@extends('admin.app')--}}

{{--@section('content')--}}
{{--    <div class="row justify-content-center" >--}}
{{--        <div class="col-md-6">--}}
{{--            <div class="clearfix">--}}
{{--                <h1 class="float-left display-3 mr-4">404</h1>--}}
{{--                <h4 class="pt-3">404 Page not Found</h4>--}}
{{--                <p class="text-muted">Sorry, the page you are looking for could not be found.</p>--}}
{{--                <strong>{{ $exception->getMessage() ?? $message ?? '' }}</strong>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Not Found</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .code {
            border-right: 2px solid;
            font-size: 26px;
            padding: 0 15px 0 15px;
            text-align: center;
        }

        .message {
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <img src="{{asset('img/logo.png')}}">
    <div class="code">
        404            </div>

    <div class="message" style="padding: 10px;">
        Not Found            </div>
</div>
</body>
</html>

