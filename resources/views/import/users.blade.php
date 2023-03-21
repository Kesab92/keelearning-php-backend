<div class="ui top attached menu">
    <div class="header item">
        Benutzer importieren
    </div>
    <div class="header item right csv-example-link">
        <a href="/import/examples/user-import" target="_blank" class="float-right">Beispiel-CSV-Datei herunterladen</a>
    </div>
</div>
<div class="ui attached container segment">
    <div class="ui form">
        {!! Form::open(['url' => '/import/users', 'method' => 'POST', 'files' => true, 'class' => 'users-import-form']) !!}
        {{ csrf_field() }}
        @if(Session::get('error-message'))
            <div class="ui error message">
                <i class="close icon"></i>
                <div class="header">Import fehlgeschlagen</div>
                <p>{{ Session::get('error-message') }}</p>
            </div>
        @endif
        @if(Session::get('type') == "users" && count($errors) > 0)
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
            <div>Erste Zeile sind Spaltenüberschriften (wird nicht importiert)</div>
            <div><?php if(count(appLanguages()) > 1): ?>4<?php else: ?>3<?php endif; ?> Felder: Vorname, Nachname, E-Mail<?php if(count(appLanguages()) > 1): ?>, Sprache ({{ join(', ', appLanguages()) }})<?php endif; ?></div>
        </div>

        <div class="field @if($errors->first('tags')) error @endif">
            <label>
                TAGs auswählen
            </label>
            <select name="tags[]" class="ui dropdown" multiple>
                <option value="">Kein TAG</option>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field @if($errors->first('groups')) error @endif">
            <label>
                Quiz-Team auswählen
            </label>
            <select name="groups[]" class="ui dropdown" multiple>
                <option value="">Kein Quiz-Team</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field @if($errors->first('file')) error @endif">
            <label>Dateiupload</label>
            <label for="userfile" class="ui icon button">
                <i class="file icon"></i>
                CSV auswählen</label>
            <input type="file" id="userfile" class="csvfile" name="file" style="display:none" accept=".csv">
            <p class="file-info">Keine Datei ausgewählt</p>
        </div>
        <button class="ui primary button submit large" type="submit">
            Benutzer importieren
        </button>
        <p>Geduld bitte, der Import kann eine Weile dauern :)</p>
        {!! Form::close() !!}
    </div>
</div>
