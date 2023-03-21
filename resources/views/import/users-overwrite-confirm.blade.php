@extends('layout._layout')

@section('main')

    <div class="content-wrapper">
      <div class="ui warning message">
        <i class="close icon"></i>
        <div class="header">CSV übernehmen</div>
        <p>Bitte überprüfen Sie sorgsam die Änderungen, bevor Sie fortfahren.</p>
      </div>
      <div class="ui top attached menu">
          <div class="header item">
              Benutzer abgleichen
          </div>
      </div>
      @if (sizeof($newUsers))
        <div class="ui attached container segment">
            <div class="ui form">
              <div class="ui teal ribbon label">Neue User</div>
              @foreach ($newUsers as $user)
                <p>{{ $user['username'] }} ({{ $user['email'] }})</p>
              @endforeach
            </div>
        </div>
      @endif
      @if (sizeof($deleteUsers))
        <div class="ui attached container segment">
            <div class="ui form">
              <div class="ui red ribbon label">Gelöschte User</div>
              @foreach ($deleteUsers as $user)
                <p>{{ $user->username }} ({{ $user->email }})</p>
              @endforeach
            </div>
        </div>
      @endif
        @if (sizeof($restoreUsers))
            <div class="ui attached container segment">
                <div class="ui form">
                    <div class="ui blue ribbon label">Wiederhergestellte User</div>
                    @foreach ($restoreUsers as $user)
                        <p>{{ $user->username }} ({{ $user->email }})</p>
                    @endforeach
                </div>
            </div>
        @endif
        @if (sizeof($updateUsers))
            <div class="ui attached container segment">
                <div class="ui form">
                    <div class="ui blue ribbon label">Meta Updates</div>
                    @foreach ($updateUsers as $user)
                        <p>{{ $user->username }} ({{ $user->email }})</p>
                    @endforeach
                </div>
            </div>
        @endif
      <div class="ui attached container segment">
          <div class="ui form">
            {!! Form::open(['url' => '/import/users-overwrite/process', 'method' => 'POST', 'class' => 'users-import-form']) !!}
              {{ csrf_field() }}
              <input type="hidden" name="payload" value="{{ $payload }}">

              <button class="ui red button submit large" type="submit" onClick="return confirm('Achtung! Dies führt die Änderungen unwiderruflich durch! Fortfahren?')">
                Änderungen anwenden
              </button>
            {!! Form::close() !!}
          </div>
      </div>
    </div>
@stop
