<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <link rel="stylesheet" href="/build/semantic.min.css">
    <link rel="stylesheet" href="{{ mix("css/login.css") }}">
    <title>
        keelearning
    </title>

    <link rel="apple-touch-icon" sizes="180x180" href="/meta/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/meta/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/meta/favicon-16x16.png">
    <link rel="manifest" href="/meta/site.webmanifest">
    <link rel="mask-icon" href="/meta/safari-pinned-tab.svg" color="#05bee6">
    <link rel="shortcut icon" href="/meta/favicon.ico">
    <meta name="msapplication-TileColor" content="#05bee6">
    <meta name="msapplication-config" content="/meta/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex">
</head>
<body>
@include('layout.partials.relaunch-redirect')
@include('layout.partials.no-old-browsers')
<div class="login-wrapper">
    <div class="ui padded text container login-container">
        <div class="column">
            <h2 class="ui login-logo">
                <img src="/img/login/logo.png?2" alt="keeunit Logo" class="image">
            </h2>
            @yield('main')
            @include('layout.partials.message-container')
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="/build/semantic.min.js"></script>
<script>
    var lastAppLogin = {{ Request::old('appid', "false") }};
</script>
<script src="{{ mix('js/login.js') }}"></script>
<script src="{{ mix('js/global.js') }}"></script>
</body>
</html>
