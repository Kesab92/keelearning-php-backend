@extends('layout._auth')

@section('main')
{{ Form::open(['url' => '/password-reset', 'class' => 'ui large form loginform emailstep']) }}
    @if(!Auth::user())
        <div class="ui segment login-main">
            <h1>
                Passwort zurücksetzen
            </h1>
            <div class="field">
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="text" name="email" class="emailinput" placeholder="E-Mail" value="{{ old('email') }}" required tabindex="1">
                </div>
            </div>
            <div class="field app-select">
                <i class="mobile alternate icon"></i>
                <select name="appid" class="ui dropdown" id="app-select"><option>App wählen</option></select>
            </div>
            <div class="ui fluid large teal submit button disabled" tabindex="4">
                Zurücksetzen
            </div>
            <div class="ui login-about">
                <a href="/login">
                    Zum Login
                </a>
            </div>
        </div>
    @else
        <div class="ui stacked segment">
            <div class="ui segment">
                Hallo {{ Auth::user()->username }}, Sie sind bereits angemeldet!
            </div>
            <a class="ui large orange button floated left" href="/logout" tabindex="1">Abmelden</a>
            <a class="ui large button floated right" href="/" tabindex="5">Zum Dashboard</a>
        </div>
    @endif
{{ Form::close() }}
@stop
