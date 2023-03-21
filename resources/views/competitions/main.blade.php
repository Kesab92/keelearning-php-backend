@extends('layout._layout')

@section('scripts')
    @include('vue-state')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/medium-editor/latest/css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8">
    <script src="/js/vendor/medium-editor.min.js"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/vue-app.js') }}"></script>
    <script src="{{ mix('js/competitions.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper">
        <h2 class="ui header">
            Gewinnspiele verwalten
        </h2>
        <div class="ui top attached menu">
            <a class="item">
                <button class="ui button basic add-competition"><i class="plus icon"></i>Neues Gewinnspiel</button>
            </a>
        </div>
        <div class="ui attached segment competitions-wrapper">
            <div class="ui divided relaxed spaced selection list competition-list">
                @if($competitions->where('quiz_team_id', '>', 0)->count())
                    <div class="item">
                        <div class="list">
                            <div class="item">
                                <i class="group icon"></i>
                                <div class="content">
                                    Quiz-Teams
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($competitions as $competition)
                        @if($competition->quiz_team_id)
                            <div class="item selection competition" data-competition-id="{{ $competition->id }}">
                                <div class="content">
                                    <div class="header">
                                        <div class="ui horizontal label views-label">
                                            @if(count($likesCounts))
                                                <span class="likes-wrapper">
                                                    <i class="heart icon"></i>
                                                    {{ isset($likesCounts[$competition->id]) ? $likesCounts[$competition->id] : 0 }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($competition->title)
                                            {{ $competition->title }}:
                                        @endif
                                        {{ $competition->quizTeam->name }} ({{ $competition->getCategoryName() }},
                                        @if ($competition->hasStartDate())
                                             bis {{ $competition->getEndDate()->format('d.m.Y H:i') }})
                                        @else
                                            Gewinnspielstart noch nicht gesetzt)
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif

                <div class="item">
                    <div class="list">
                        <div class="item">
                            <i class="tags icon"></i>
                            <div class="content">
                                TAGs
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($competitions as $competition)
                    @if($competition->tags->count() > 0)
                        <div class="item selection competition" data-competition-id="{{ $competition->id }}">
                            <div class="content">
                                <div class="header">
                                    <div class="ui horizontal label views-label">
                                        @if(count($likesCounts))
                                            <span class="likes-wrapper">
                                                <i class="heart icon"></i>
                                                {{ isset($likesCounts[$competition->id]) ? $likesCounts[$competition->id] : 0 }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($competition->title)
                                        {{ $competition->title }}:
                                    @endif
                                    @foreach($competition->tags as $tag)
                                        {{ $tag->label }}
                                    @endforeach
                                    @if ($competition->hasStartDate())
                                        ({{ $competition->getCategoryName() }}, bis {{ $competition->getEndDate()->format('d.m.Y H:i') }})
                                    @else
                                        ({{ $competition->getCategoryName() }}, Gewinnspielstart noch nicht gesetzt)
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="competition-content ui basic segment">
            </div>
        </div>
    </div>

    <div class="ui add-competition-modal modal">

            <i class="close icon"></i>
            <div class="header">
                Neues Gewinnspiel erstellen
            </div>
            <div class="content">
                <p>
                    Wählen Sie TAGs, Fragenkategorie und Dauer aus. Die Benutzer des Quiz-Teams werden per Email benachrichtigt, sobald das Gewinnspiel startet.
                </p>
            </div>
            <div class="content">
                <form id="new-competition-form" class="ui form" action="/competitions" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="field required">
                        <label>TAGs</label>
                        <select name="tags[]" id="tag-select" class="ui dropdown" multiple required>
                            <option value="">TAG wählen</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field required">
                        <label>Titel</label>
                        <input type="text" placeholder="Titel" name="title" required>
                    </div>
                    <div class="field required">
                        <label>Fragenkategorie</label>
                        <select name="category" id="category-select" class="ui fluid dropdown" required>
                            <option value="null" selected>Alle Kategorien</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ui two column grid">
                        <div class="column">
                            <div class="field required">
                                <label>Gewinnspielstart</label>
                                <div class="ui calendar" id="startCompetition">
                                    <div class="ui input left icon">
                                        <i class="calendar icon"></i>
                                        <input type="text" name="start_at" required placeholder="Gewinnspielstart">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field required">
                                <label>Dauer (in Tagen)</label>
                                <input type="number" name="duration" value="7" min="1" required title="Die Mindestdauer beträgt einen Tag">
                            </div>
                        </div>
                    </div>
                    <div class="field required">
                        <label>Beschreibung</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <input type="submit" class="ie-submit-fix new-competition-form" style="display: none;">
                </form>
            </div>
            <div class="actions">
                <button class="ui close close-modal button">Abbrechen</button>
                <button class="ui green button approve new-competition-form" disabled>Gewinnspiel erstellen</button>
            </div>
    </div>

@stop
