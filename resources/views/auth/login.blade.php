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
</head>
<body class="app header-fixed text-center">
    <!-- Header -->
    <header class="app-header row justify-content-center pull-left" style="padding-top: 35px;">
        <img class="center" src="img/logo.png">
    </header>

    <!-- Main contents -->
    <div class="app-body">
        <div class="container mt-6 text-center" style="margin-top: 130px;" >
            <div class="row">
                <div class="col-md-12">
                    <div class="card-group">
                        <div class="card p-4">
                            <div class="card-body">
                                <h1>Login - Valedra Backend </h1>
                                <div class="row">
                                    <div class="col-12 mt-5">
                                        <a href="{{ url('/redirect') }}" class="btn btn-danger">Login With Google</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>