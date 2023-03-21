@extends('layout._layout')

@section('scripts')
    @if (isset($preScripts))
        @foreach ($preScripts as $script)
            <script src="{{ $script }}"></script>
        @endforeach
    @endif
    @include('vue-state')
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/vue-app.js') }}"></script>
    @if (isset($scripts))
        @foreach ($scripts as $script)
            <script src="{{ $script }}"></script>
        @endforeach
    @endif
    @if (isset($styles))
        @foreach ($styles as $style)
            <link href="{{ $style }}" rel="stylesheet">
        @endforeach
    @endif

    <link href="https://amp.azure.net/libs/amp/latest/skins/amp-default/azuremediaplayer.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/vendor/static/vuetify.min.css?v=3" />
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i|Noto+Sans:400,400i,700,700i|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i|Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i|Ubuntu:300,300i,400,400i,500,500i,700,700i&display=swap" rel="stylesheet">
    <script src="https://amp.azure.net/libs/amp/latest/azuremediaplayer.min.js"></script>

@stop

@section('main')
    <div class="content-wrapper content-wrapper--vue @if ($hasFluidContent) content-wrapper-fluid @endif" id="app">
        <vuetify-wrapper class="content-wrapper">
            <{{ $component }}
                @if (isset($props))
                    @foreach ($props as $prop => $value)
                        :{{ $prop }}="{{ json_encode($value) }}"
                    @endforeach
                @endif
            ></{{ $component }}>
        </vuetify-wrapper>
    </div>
@stop

