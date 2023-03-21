@extends('layout._layout')

@section('scripts')
    <script src="{{ mix('js/stats.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper stats-wrapper">
        <div class="ui message transition" data-msg-permanent="stat-learn-info">
            <i class="close icon"></i>
            <div class="header">
                So funktioniert der Leitner-Algorithmus
            </div>
            <p>
                Jede Frage ist nach 6 Wiederholungen innerhalb von 54 Tagen im Langzeitgedächtnis verankert. Die Fragen durchlaufen dabei diverse „Boxen“: nach dem ersten Durchgang werden die Fragen in Box 1 abgelegt und werden Ihnen am nächsten Tag zum Lernen vorgelegt. Danach kommen sie in Box 2; von dort werden die Fragen alle 2 Tage gelernt. Die Fragen aus Box 3 werden alle 7 Tage, die aus Box 4 alle 15 Tage und aus Box 5 alle 30 Tage wiederholt. Das System erkennt während des Lernens Ihren erworbenen Wissensstand und weist Ihnen entsprechend Ihrem Lernfortschritt neue Lernfragen zu. Dabei bezieht das System die gegeben Antworten aller User mit ein, um zu bewerten, ob eine Frage schwer oder leicht ist.
            </p>
            <div class="header">
                So lesen Sie die Statistik
            </div>
            <p>
                Die Zahl stellt das Mittel aller fünf Boxen dar. Darunter sehen Sie farblich die Verteilung je Box.<br>
                Laden Sie sich auf der rechten Seite die csv-Datei herunter. Hier finden Sie dann die exakten Werte pro Box.
            </p>
        </div>
        <div class="ui top attached tabular menu">
            <a href="{{ route('stats.training.players') }}" class="item @if($type == 'players') active @endif" style="cursor: pointer;">
                Benutzer
            </a>
            <div class="right menu">
                @if($type == 'players')
                    <div class="item">
                        <span style="margin-right: 10px">Benutzer filtern:</span>
                        <select class="user-tag-select ui normal dropdown">
                            <option value="0" @if(!$selectedTag) selected @endif>Alle</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @if($selectedTag == $tag->id) selected @endif>
                                    TAG: {{ $tag->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="item">
                    <a href="/stats/training/csv/{{ $type }}" target="_blank" class="csv-download-button ui labeled icon button">
                        <i class="cloud download icon"></i>
                        Excel Download
                    </a>
                </div>
            </div>
        </div>
        @if($type == 'players')
            <form method="GET" class="stats-player-filter">
                <input type="hidden" name="tag" value="{{ $selectedTag }}">
                <input type="hidden" name="sort" value="{{ $sortBy }}">
                <input type="hidden" name="sortDesc" value="{{ $sortDesc }}">
            </form>
        @endif
        <div class="active ui bottom attached tab segment">
            @include ('stats.training.' . $type)
        </div>

        @if($settings->getValue('save_user_ip_info'))
          <div class="ui segment">
            This product includes GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.
          </div>
        @endif
    </div>

@stop
