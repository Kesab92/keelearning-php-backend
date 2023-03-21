<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ $title ?? __('app_message.please_wait') }}</title>

    <style>
        html,
        body {
            background: rgb(31, 31, 49);
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        body {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .container {
            background: {{ $appProfile->getValue('color_white') }};
            border-radius: 16px;
            box-sizing: border-box;
            color: {{ $appProfile->getValue('color_dark') }};
            font-size: 20px;
            max-width: calc(100vw - 32px);
            padding: 16px;
            width: 400px;
        }

        .container.-error .message {
            color: {{ $appProfile->getValue('color_error') }};
        }

        .redirect-message {
            font-size: 16px;
            margin-top: 8px;
        }

        .redirect-message a {
            color:  {{ $appProfile->getValue('color_dark_light_emphasis') }};
            display: block;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container {{ isset($isError) && $isError ? '-error' : '' }}">
        <div class="message">
            {{ $message }}
        </div>
        <div class="redirect-message">
            {{ __('app_message.you_will_be_redirected') }}
            <a href="{{ $url ?? $appProfile->app_hosted_at }}">
                {{ __('app_message.click_here_for_redirect') }}
            </a>
        </div>
    </div>
    <script>
        window.setTimeout(function() {
            window.location = '{{ $url ?? $appProfile->app_hosted_at }}';
        }, 10 * 1000);
    </script>
</body>
</html>
