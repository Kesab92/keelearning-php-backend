@extends('layout._layout')

@section('scripts')
    <script src="{{ mix('js/stats.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper stats-wrapper">
        <div class="ui message transition" data-msg-permanent="stat-learn-info">
            <i class="close icon"></i>
            <div class="header">
                So funktioniert das Powerlearning
            </div>
            <p>
                Im Trainingsmodus muss eine Frage drei mal richtig beantwortet werden, um als "gelernt" zu gelten. Das System stellt nur Fragen, die noch nicht als gelernt markiert sind. Es gelten keine zeitlichen Limits oder Beschränkungen.
            </p>
            <div class="header">
                So lesen Sie die Statistik
            </div>
            <p>
                Die Zahl stellt das Mittel aller vier Boxen dar. Darunter sehen Sie farblich die Verteilung je Box.<br>
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
            @include ('stats.training.' . $type . '-powerlearning')
        </div>

        @if($settings->getValue('save_user_ip_info'))
          <div class="ui segment">
            This product includes GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.
          </div>
        @endif
    </div>

@stop
