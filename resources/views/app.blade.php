<?php ?>
<!--
 * CoreUI Pro - Bootstrap 4 Admin Template
 * @version v1.0.4
 * @link http://coreui.io/pro/
 * Copyright (c) 2017 creativeLabs Łukasz Holeczek
 * @license http://coreui.io/pro/license/
 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Valedra Backend">
    <meta name="robots" content="noarchive">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="img/favicon.png">
    <title>Valedra Backend</title>

    <!-- Icons -->
    <link href="{{ URL::asset('vendors/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/simple-line-icons.min.css') }} " rel="stylesheet">

    <!-- Main styles for this application -->
    <link href="{{ URL::asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('css/custom.css') }}" rel="stylesheet">

    <!-- Styles required by this views -->
    <link href="{{ URL::asset('vendors/css/daterangepicker.min.css') }} " rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/gauge.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/toastr.min.css') }}" rel="stylesheet">

    <!-- Styles required by this views -->
    <link href="{{ URL::asset('vendors/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/select2.min.css') }}" rel="stylesheet">

    <!-- Styles required by this views -->
    <link href="{{ URL::asset('vendors/css/ladda-themeless.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/spinkit.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendors/css/toastr.min.css') }}" rel="stylesheet">

</head>

<!-- BODY options, add following classes to body to change options

// Header options
1. '.header-fixed'					- Fixed Header

// Brand options
1. '.brand-minimized'       - Minimized brand (Only symbol)

// Sidebar options
1. '.sidebar-fixed'					- Fixed Sidebar
2. '.sidebar-hidden'				- Hidden Sidebar
3. '.sidebar-off-canvas'		- Off Canvas Sidebar
4. '.sidebar-minimized'			- Minimized Sidebar (Only icons)
5. '.sidebar-compact'			  - Compact Sidebar

// Aside options
1. '.aside-menu-fixed'			- Fixed Aside Menu
2. '.aside-menu-hidden'			- Hidden Aside Menu
3. '.aside-menu-off-canvas'	- Off Canvas Aside Menu

// Breadcrumb options
1. '.breadcrumb-fixed'			- Fixed Breadcrumb

// Footer options
1. '.footer-fixed'					- Fixed footer

-->

{{--<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">--}}
<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden pace-done">

<!-- Header -->
<header class="app-header navbar">
    @yield('header')
</header>

<!-- Main contents -->
<div class="app-body">
    @yield('body')
</div>

<!-- Footer -->
<footer class="app-footer">
    @yield('footer')
</footer>

<!-- Bootstrap and necessary plugins -->
<script src="{{ URL::asset('vendors/js/jquery.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/popper.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/pace.min.js') }}"></script>

<!-- Plugins and scripts required by all views -->
<script src="{{ URL::asset('vendors/js/Chart.min.js') }}"></script>

<!-- CoreUI Pro main scripts -->
<script src="{{ URL::asset('js/app.js') }}"></script>

<!-- Plugins and scripts required by this views -->
<script src="{{ URL::asset('vendors/js/toastr.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/gauge.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/moment.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/daterangepicker.min.js') }}"></script>

<!-- Custom scripts required by this view -->
<script src="{{ URL::asset('js/views/main.js') }}"></script>

<!-- Plugins and scripts required by this views -->
<script src="{{ URL::asset('vendors/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- Custom scripts required by this view -->
<script src="{{ URL::asset('js/views/tables.js') }}"></script>

<!-- Plugins and scripts required by this views -->
<script src="{{ URL::asset('vendors/js/jquery.validate.min.js') }}"></script>

<!-- Custom scripts required by this view -->
<script src="{{ URL::asset('js/views/validation.js') }}"></script>

<script src="{{ URL::asset('vendors/js/jquery.maskedinput.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/daterangepicker.min.js') }}"></script>
<script src="{{ URL::asset('js/views/advanced-forms.js') }}"></script>
<script src="{{ URL::asset('vendors/js/toastr.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

<script language="Javascript" type="text/javascript">
    var _Payload = {};
</script>

@yield('footer-js')

<script src="{{ URL::asset('js/admin/common.js') }}"></script>

</body>
</html>