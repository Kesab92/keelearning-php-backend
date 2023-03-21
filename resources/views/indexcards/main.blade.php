@extends('layout._layout')

@section('scripts')
    <script src="/js/vendor/medium-editor.min.js"></script>
    <script src="/js/vendor/interact.min.js"></script>
    <script src="{{ mix('js/indexcards.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper">
        <h2 class="ui header">
            Karten verwalten
        </h2>
        <div class="ui top attached menu">
            <a class="item">
                <button class="ui button basic add-indexcard"><i class="plus icon"></i>Neue Karte</button>
            </a>
            <a class="item" target="_blank" href="/indexcards/export">
                <button class="ui button basic"><i class="download icon"></i>Exportieren</button>
            </a>
            <form action="/indexcards" method="get" class="right menu indexcard-search ui transparent icon input" style="border: 0">
                    <div class="ui right aligned category search item">
                        <div class="ui action input">
                            <input name="query" type="text" value="{{ $query }}" placeholder="Karteninhalt">
                            <button class="ui icon button">
                                <i class="search icon"></i>
                            </button>
                        </div>
                        <div class="results"></div>
                    </div>
            </form>
        </div>
        <div class="ui attached segment indexcards-wrapper">
            <div class="ui divided relaxed spaced selection list indexcard-list">
                @foreach($indexcards as $indexcard)
                    <div class="item selection indexcard" data-indexcard-id="{{ $indexcard->id }}">
                        <div class="content">
                            <div class="header">
                                {{ strip_tags($indexcard->front) }}
                                <div class="ui blue horizontal label">{{ $indexcard->category?$indexcard->category->name:'Keine Kategorie!' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if(!$indexcards->count())
                    <div class="ui orange segment">Keine Karten gefunden</div>
                @endif
            </div>
            <div class="indexcard-content ui basic segment">
            </div>
            {!! $indexcards->appends(['query' => $query])->render() !!}
        </div>
    </div>

    <div class="ui add-indexcard-modal modal">

        <i class="close icon"></i>
        <div class="header">
            Neue Karteikarte erstellen
        </div>
        <div class="content">
            <form id="new-indexcard-form" class="ui form" action="/indexcards" method="POST">
                {{ csrf_field() }}
                <div class="field">
                    <label>Vorderseite</label>
                    <div class="ui right labeled input">
                        <textarea name="front" rows="3" required></textarea>
                    </div>
                </div>
                <div class="field">
                    <label>Rückseite</label>
                    <div class="ui right labeled input">
                        <textarea name="back" rows="3" required></textarea>
                    </div>
                </div>
                <div class="field">
                    <label>Kategorie</label>
                    <div class="ui right labeled input">
                        <select required name="category" class="ui fluid dropdown">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>Typ</label>
                    <div class="ui right labeled input">
                        <select required name="type" class="ui fluid dropdown">
                            @foreach($types as $type => $name)
                                <option value="{{ $type }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="submit" class="ie-submit-fix new-indexcard-form" style="display: none;">
            </form>
        </div>
        <div class="actions">
            <button class="ui close close-modal button">Abbrechen</button>
            <button type="submit" class="ui green button approve new-indexcard-form" form="new-indexcard-form">Karte erstellen</button>
        </div>
    </div>

@stop
