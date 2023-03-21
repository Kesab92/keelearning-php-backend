<div class="ui top attached menu">
    <div class="header item">
        Fragen importieren
    </div>
    <div class="header item right csv-example-link">
        <div class="ui dropdown filedropdown">
            <input type="hidden" name="fileType">
            <div class="default text file-dropdown">Beispiel-CSV-Datei herunterladen</div>
            <i class="dropdown icon"></i>
            <div class="menu">
                <div class="item" data-value="single">
                    <a href="/demo-imports/demo-import-questions-single.csv">Single-Choice Beispiel</a>
                </div>
                <div class="item" data-value="boolean">
                    <a href="/demo-imports/demo-import-questions-boolean.csv">Richtig/Falsch Beispiel</a>
                </div>
                <div class="item" data-value="multiple">
                    <a href="/demo-imports/demo-import-questions-multiple.csv">Multiple-Choice Beispiel</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ui attached container segment">
    <div class="ui form">
        {!! Form::open(['url' => '/import/questions', 'method' => 'POST', 'files' => true, 'class' => 'questions-import-form']) !!}
        {{ csrf_field() }}
        @if(Session::get('type') == "questions" && count($errors) > 0)
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
            <div>Kommagetrennt, mindestens 3 Spalten</div>
            <div>Erste Zeile sind Spaltenüberschriften (wird nicht importiert)</div>
            <br>
            <div class="header">Eigenschaften von "{{ $app->name }}"</div>
            <div>Runden pro Spiel: {{ $app->rounds_per_game }}</div>
            <div>Fragen pro Runde: {{ $app->questions_per_round }}</div>
            <div>Antworten pro Frage: {{ $app->answers_per_question }}</div>
        </div>

        <div class="field @if($errors->first('category')) error @endif">
            <label>
                Kategorie für Fragen auswählen
            </label>
            <select name="category" class="ui dropdown" required>
                <option value="">Kategorien</option>
                @foreach($categories as $key => $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field @if($errors->first('file')) error @endif">
            <label>Dateiupload <i class="icon info circle" data-html="
                    <div class='header'>
                        CSV Datei
                    </div>
                    <div class='content'>
                        Fragen können aus Dateien mit der Endung .csv importiert werden. Pro Zeile müssen darin die Frage
                        und die Antworten mit Kommata getrennt stehen. Die erste Antwort ist dabei die Richtige. <br><br>
                        Beispiel: <br>
                        Was ergibt 1+1?, 2, 10, 15, 20 <br>
                        Was ergibt 2+2?, 4, 20, 30, 40 <br><br>
                    </div>
                    <div class='meta'>
                        (Zu beachten: Die Anzahl der Antworten muss gleich der Antworten pro Frage sein)
                    </div>"
                                  data-variation="small"></i></label>
            <label for="questionfile" class="ui icon button">
                <i class="file icon"></i>
                CSV hochladen</label>
            <input type="file" id="questionfile" class="csvfile" name="file" style="display:none" accept=".csv">
            <p class="file-info">Keine Datei ausgewählt</p>
        </div>
        <div class="field">
            <label>
                Fragentyp
            </label>
            <select name="questiontype" class="ui dropdown">
                <option value="{{ App\Models\Question::TYPE_SINGLE_CHOICE }}">Single Choice</option>
                <option value="{{ App\Models\Question::TYPE_MULTIPLE_CHOICE }}">Multiple Choice</option>
                <option value="{{ App\Models\Question::TYPE_BOOLEAN }}">Richtig / Falsch</option>
            </select>
        </div>
        <button class="ui primary button submit large" type="submit">
            Fragen importieren
        </button>
        {!! Form::close() !!}
    </div>
</div>
