@if(isset($meta['formId']))
    Formular ID: {{ $meta['formId'] }}<br>
@endif
@if(isset($meta['selectedLanguage']))
    Selected language: {{ $meta['selectedLanguage'] }}<br>
@endif
@if(isset($meta['differences']))
    {{ json_encode($meta['differences'], JSON_UNESCAPED_UNICODE) }}
@else
    {{ json_encode($meta, JSON_UNESCAPED_UNICODE) }}
@endif
