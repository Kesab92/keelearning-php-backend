Hallo {{ $email }},

Sie erhalten Ihre aktuelle Statistik aus der {{ $app->app_name }}.
@if($tags)
TAGs: {{ $tags }}
@endif

Zeitraum: {{ $interval }}

Viele Grüße
Ihr {{ $app->app_name }} Team

Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: {{ $app->getDefaultAppProfile()->getValue('contact_email') }} | {{ $app->getDefaultAppProfile()->getValue('contact_phone') }}
