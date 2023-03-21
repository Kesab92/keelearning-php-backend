<div class="ui top attached menu">
  <div class="header item">
    Benutzer löschen
  </div>
  <div class="header item right csv-example-link">
    <a href="/demo-imports/demo-import-users-delete.csv" class="float-right">Beispiel-CSV-Datei herunterladen</a>
  </div>
</div>
<div class="ui attached container segment">
<div class="ui form">
  {!! Form::open(['url' => '/import/users-delete/preview', 'method' => 'POST', 'files' => true, 'class' => 'users-import-form']) !!}
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
      <div>Identifikation erfolgt über E-Mail</div>
      <div>Eine Mail-Adresse pro Zeile</div>
    </div>

    <div class="field @if($errors->first('file')) error @endif">
      <label>Dateiupload</label>
      <label for="userdeletefile" class="ui icon button">
      <i class="file icon"></i>
      CSV auswählen</label>
      <input type="file" id="userdeletefile" class="csvfile" name="file" style="display:none" accept=".csv">
      <p class="file-info">Keine Datei ausgewählt</p>
    </div>
    <button class="ui primary button submit large" type="submit">
      Vorschau
    </button>
  {!! Form::close() !!}
  </div>
</div>
