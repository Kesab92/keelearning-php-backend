<div class="ui top attached menu">
  <div class="header item">
    Benutzer abgleichen
  </div>
  <div class="header item right csv-example-link">
    <a href="/import/examples/user-diff" target="_blank" class="float-right">Beispiel-CSV-Datei herunterladen</a>
  </div>
</div>
<div class="ui attached container segment">
<div class="ui form">
  {!! Form::open(['url' => '/import/users-overwrite/preview', 'method' => 'POST', 'files' => true, 'class' => 'users-import-form']) !!}
    {{ csrf_field() }}
    @if(Session::get('error-message'))
      <div class="ui error message">
        <i class="close icon"></i>
        <div class="header">Import fehlgeschlagen</div>
        <p>{{ Session::get('error-message') }}</p>
      </div>
    @endif
    @if(Session::get('type') == "users-overwrite" && count($errors) > 0)
      <div class="ui error message">
        <i class="close icon"></i>
        <div class="header">Fehler</div>
        @foreach($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif
    <div class="ui warning message">
      <i class="close icon"></i>
      <div class="header">CSV Format</div>
      <div>Kommagetrennt</div>
      <div style="z-index: 20;position:relative;">
          Identifikation erfolgt über
          @if(!count($app->getUserMetaDataFields(true)))
            E-Mail
            <input type="hidden" name="comparisonKey" value="email" />
          @else
            <select name="comparisonKey" class="ui dropdown">
                <option value="email">E-Mail</option>
                @foreach($app->getUserMetaDataFields(true) as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
          @endif
      </div>
      <div>Erste Zeile sind Spaltenüberschriften (wird nicht importiert)</div>
      <div>{{ 3 + count($app->getUserMetaDataFields(true)) }} Felder: Vorname, Nachname, E-Mail<?php if(count($app->getUserMetaDataFields(true))): ?>, {{  join(', ', array_keys($app->getUserMetaDataFields(true))) }}<?php endif; ?></div>
    </div>

    <div class="field @if($errors->first('file')) error @endif">
      <label>Dateiupload</label>
      <label for="useroverwritefile" class="ui icon button">
      <i class="file icon"></i>
      CSV auswählen</label>
      <input type="file" id="useroverwritefile" class="csvfile" name="file" style="display:none" accept=".csv">
      <p class="file-info">Keine Datei ausgewählt</p>
    </div>
    <button class="ui primary button submit large" type="submit">
      Vorschau
    </button>
  {!! Form::close() !!}
  </div>
  <p>Geduld bitte, der Import kann eine Weile dauern :)</p>
</div>
