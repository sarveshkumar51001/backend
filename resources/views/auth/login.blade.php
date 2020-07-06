<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Valedra Backend">
    <meta name="robots" content="noarchive">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Valedra Backend login</title>

    <!-- Main styles for this application -->
    <link href="{{ URL::asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('css/custom.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ URL::asset('img/favicon.ico') }}" type="image/x-icon">
	<link rel="icon" href="{{ URL::asset('img/favicon.ico') }}" type="image/x-icon">
    <style>
        body {
            background-image: url("https://picsum.photos/1920/1080");
        }
    </style>
</head>
<body class="app flex-row align-items-center  pace-done"><div class="pace  pace-inactive"><div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
        <div class="pace-progress-inner"></div>
    </div>
    <div class="pace-activity"></div></div>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-group" style="opacity:0.85;">
                <div class="card py-5">
                    <div class="card-body text-center">
                        <img class="center" src="img/logo.png">
                        <h1>Backend APP</h1>
                    </div>
                </div>
                <div class="card py-4">
                    <div class="card-body p-5">
                        <h1>Login</h1>
                        <p class="text-muted">Sign In to your account</p>
                            <a href="{{ url('/redirect') }}" class="btn btn-danger">Login With Google</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
