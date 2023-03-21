<div class="ui top attached menu">
    <div class="header item">
        Karteikarten importieren
    </div>
    <div class="header item right csv-example-link">
        <a href="/demo-imports/demo-import-index-cards.csv" target="_blank" class="float-right">Beispiel-CSV-Datei herunterladen</a>
    </div>
</div>
<div class="ui attached container segment">
    <div class="ui form">
        {!! Form::open(['url' => '/import/cards', 'method' => 'POST', 'files' => true, 'class' => 'cards-import-form']) !!}
        {{ csrf_field() }}
        @if(Session::get('error-message'))
            <div class="ui error message">
                <i class="close icon"></i>
                <div class="header">Import fehlgeschlagen</div>
                <p>{{ Session::get('error-message') }}</p>
            </div>
        @endif
        @if(Session::get('type') == "cards" && count($errors) > 0)
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
            <div>2 Felder: Rückseite, Vorderseite</div>
        </div>

        <div class="field @if($errors->first('category') && Session::get('type') == "cards") error @endif">
            <label>
                Kategorie für Karten auswählen
            </label>
            <select name="category" class="ui dropdown" required>
                <option value="">Kategorie</option>
                @foreach($categories as $key => $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field @if($errors->first('file')) error @endif">
            <label>Dateiupload</label>
            <label for="cardsfile" class="ui icon button">
                <i class="file icon"></i>
                CSV auswählen</label>
            <input type="file" id="cardsfile" class="csvfile" name="file" style="display:none" accept=".csv">
            <p class="file-info">Keine Datei ausgewählt</p>
        </div>
        <button class="ui primary button submit large" type="submit">
            Karten importieren
        </button>
        {!! Form::close() !!}
    </div>
</div>
