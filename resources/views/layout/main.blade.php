<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Styles -->
    {{--<link href="/css/app.css" rel="stylesheet">--}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ $CDN_URL }}images/favicon-32x32.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PAS Dashboard</title>
    <link href="{{$CDN_URL}}dashboard/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="{{$CDN_URL}}dashboard/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="{{$CDN_URL}}dashboard/css/bootstrap.css" rel="stylesheet" media="all">
    <link href="{{$CDN_URL}}dashboard/css/animsition.min.css" rel="stylesheet" media="all">
    <link href="{{$CDN_URL}}dashboard/css/perfect-scrollbar.css" rel="stylesheet" media="all">
    <link href="{{$CDN_URL}}dashboard/css/dashboard-style.css" rel="stylesheet" media="all">
    <link href="/css/dashboard-style.css" rel="stylesheet" media="all">
    @yield('myCssFiles')
</head>

<body class="animsition">

<div class="page-wrapper">
    @include('layout.dashboard.left')
    @include('layout.flash-message')
    <div class="page-container">
        @include('layout.dashboard.header')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</div>

<script src="{{$CDN_URL}}dashboard/js/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="{{$CDN_URL}}dashboard/js/bootstrap.min.js"></script>
<script src="{{$CDN_URL}}dashboard/js/animsition.min.js"></script>
<script src="{{$CDN_URL}}dashboard/js/perfect-scrollbar.js"></script>
<script src="{{$CDN_URL}}dashboard/js/main.js"></script>

<script src="/js/app.js?v=1.2"></script>

@yield('myJsFile')

</body>
</html>