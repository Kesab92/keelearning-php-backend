<!DOCTYPE html>
<html>
<head>
    <style type="text/css">
        html, body {
            padding: 0;
            margin: 0;
            height: 100%;
            width: 100%;
            min-height: 100%;
            font-family: Arial;
        }
        body {
            background-size: {{ $size['width'] }}mm {{ $size['height'] }}mm;
            background-image: url('{{ $template->background_image_url }}');
        }

        p {
            line-height: 1.2; /* mce line height */
            margin: 0;
            padding: 0;
        }

        .element {
            line-height: 1.5; /* vuetify line height */
            position: absolute;
            font-size: {{ $baseFontSize }}mm;
        }
    </style>
</head>
<body>
    @foreach($elements as $element)
        <div class="element" style="left: {{ $element['left'] }}%;top: {{ $element['top'] }}%;width: {{ $element['width'] }}%;height: {{ $element['height'] }}%;">{!! $element['text'] !!}</div>
    @endforeach
</body>
</html>
