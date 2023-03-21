@extends('layout._auth')

@section('main')
<script>
    if (window !== window.parent) {
        document.body.style.background = '#1f1f30'
        document.querySelector('.login-wrapper').style.opacity = '0'
    }
</script>
{{ Form::open(['url' => '/login', 'class' => 'ui large form loginform emailstep']) }}
    @if(!Auth::user())
        <div class="ui segment login-main">
            <h1>
                Login
            </h1>
            <div class="field">
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="text" name="email" class="emailinput" placeholder="E-Mail" value="{{ old('email') }}" required tabindex="1">
                </div>
            </div>
            <div class="field password-field disabled">
                <div class="ui left icon input">
                    <i class="lock icon"></i>
                    <input type="password" name="password" placeholder="Passwort" required tabindex="2">
                </div>
                <a href="/password-reset" class="password-reset-link">
                    Passwort vergessen?
                </a>
            </div>
            <div class="field app-select">
                <i class="mobile alternate icon"></i>
                <select name="appid" class="ui dropdown" id="app-select"><option>App wählen</option></select>
            </div>
            <div class="field remember-field disabled">
                <div class="ui checkbox">
                    <input type="checkbox" id="rememberme" name="remember" tabindex="3">
                    <label for="rememberme">Angemeldet bleiben</label>
                </div>
            </div>
            <div class="ui fluid large teal submit button disabled" tabindex="4">Login</div>
            <div class="ui login-about">
                Ein Angebot der <a href="https://keeunit.de">keeunit GmbH</a>
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
