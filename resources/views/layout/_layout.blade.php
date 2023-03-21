<!DOCTYPE html>
<html lang="de">
    <head>
        <!-- Required meta tags always come first -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <!-- App CSS -->
        <link rel="stylesheet" href="/build/semantic.min.css?v=2">
        <link rel="stylesheet" href="{{ mix("css/app.css") }}">
        <link rel="stylesheet" href="{{ mix("css/vendor/dropzone.css") }}">
        <link rel="stylesheet" href="{{ mix("css/vendor/spectrum.css") }}">
        <link rel="stylesheet" href="{{ mix("css/vendor/calendar.css") }}">
        <link rel="stylesheet" href="/css/vendor/static/jquery-ui-smoothness-1.12.0.min.css">
        <link href='https://fonts.googleapis.com/css?family=Material+Icons' rel="stylesheet">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="app-default-language" content="{{ defaultAppLanguage() }}">
        <meta name="app-default-language-emoji" content="{{ Emoji::getLanguageFlag(defaultAppLanguage()) }}">

        <title>keelearning Admin</title>

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
        @include('layout.partials.icons')
        @include('layout.partials.header')

        <div id="content">

            @include('layout.partials.sidenav')

            @include('layout.partials.message-container')

            @if(Auth::user() && Auth::user()->isSuperAdmin())
                <div style="padding: 5px;background: white;">
                    {{ App\Models\App::find(appId())->getAppNameAttribute() }}
                </div>
            @endif

            @yield('main')

        </div>

        @include('layout.partials.footer')

        <script src="/js/static/jquery-2.2.4.min.js"></script>
        <script src="/js/static/jquery-ui-1.12.0.min.js"></script>

        <script src="/build/semantic.min.js"></script>
        @include('vue-state')
        <script src="{{ mix('js/global.js') }}"></script>

        @yield('scripts')

        @include('layout.partials.tracking')
    </body>
</html>
