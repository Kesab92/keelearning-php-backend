@if(isset($meta['questionId']))
    Frage ID: {{ $meta['questionId'] }}<br>
@endif
@if(isset($meta['differences']))
    {{ json_encode($meta['differences'], JSON_UNESCAPED_UNICODE) }}
@else
    {{ json_encode($meta, JSON_UNESCAPED_UNICODE) }}
@endif
