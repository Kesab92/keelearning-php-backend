@extends('layout._layout')

@section('scripts')
    <script src="{{ mix('js/tests.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper">
        <div class="loading-screen">
            <div class="ui basic segment loading"></div>
        </div>
        <h2 class="ui header">
            Tests
        </h2>
        <div class="ui top attached menu">
            <a class="item">
                <button class="ui button basic add-test"><i class="plus icon"></i>Neuer Test</button>
            </a>
            <form action="/tests" method="get" class="right menu user-search ui transparent icon input" style="border: 0">
                <div class="ui right aligned category item" style="min-width: 200px">
                    <select name="filter" class="filter-search ui dropdown fluid">
                        <option @if ($filter === '-') selected @endif value="-">Aktive Tests</option>
                        <option @if ($filter === 'archived') selected @endif value="archived">Archivierte Tests</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="ui attached segment tests-wrapper">
            <div class="ui large divided relaxed spaced selection list test-list">
                @foreach($tests as $test)
                    <a href="/tests/{{ $test->id }}" class="item selection test" data-test-id="{{ $test->id }}">
                        <span class="right floated content">
                            @if (!$test->archived)
                                <button class="ui button approve archive-test" data-test-id="{{ $test->id }}">Archivieren</button>
                            @endif
                            <button class="ui results button remove-test red"
                                data-test-id="{{ $test->id }}"
                                data-test-submissions="{{ $test->submissions()->count() }}">
                                Löschen
                            </button>
                        </span>
                        <span class="right floated content">
                            <button class="ui results button test-results" data-test-id="{{ $test->id }}">Statistik</button>
                        </span>
                        <div class="content">
                            <div class="header">
                                {{ $test->name }}
                            </div>
                            @if($test->hasEndDate())
                                Aktiv bis zum {{ $test->active_until->format("d.m.Y H:i:s") }}
                            @endif
                            <div>
                                @foreach($test->tags as $tag)
                                    <div class="ui blue horizontal label">{{ $tag->label }}</div>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach

                @if (count($tests) == 0)
                    <div class="item selection">Keine Tests gefunden.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="ui remove-test-modal modal">
        <input type="hidden" name="test-id">
        <i class="close icon"></i>
        <div class="header">Test löschen</div>
        <div class="content">
            <p>
                Neben dem Test werden auch noch weitere Abhängigkeiten gelöscht:<br>
                Es werden<br>
                <span class="submission-count"></span>x Testergebnisse<br>
                <span class="certificate-count"></span>x Zertifikatsvorlage gelöscht<br>
                Möchten Sie den Test wirklich löschen?
            </p>
        </div>
        <div class="actions">
            <button class="ui close close-modal button">Abbrechen</button>
            <button class="ui red button approve delete-test">Löschen</button>
        </div>
    </div>

    <div class="ui add-test-modal modal">
        <i class="close icon"></i>
        <div class="header">
            Neuen Test erstellen
        </div>
        <div class="content">
            <p>
                Geben Sie dem Test hier einen Namen und @if(!$hasLimitedTAGAccess) optional @endif TAGs. Anschließend können Sie die Fragen festlegen.
            </p>
        </div>
        <div class="content">
            <form id="new-test-form" class="ui form" action="/tests" method="POST">
                {{ csrf_field() }}
                <div class="field">
                    <label>Name des Tests</label>
                    <input type="text" name="name" required>
                </div>
                <div class="field @if($hasLimitedTAGAccess) required @endif">
                    <label>TAGs</label>
                    <select name="tag_ids[]" @if($hasLimitedTAGAccess) required @endif multiple class="tag-search ui dropdown fluid">
                        <option value="">TAG wählen</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">
                                {{ $tag->label }}
                            </option>
                        @endforeach
                    </select>
                    @if($hasLimitedTAGAccess) Wählen Sie mindestens einen TAG aus. @endif
                </div>
                <div class="field required">
                    <label>Test-Typ</label>
                    <select name="mode" id="mode-select" class="ui fluid dropdown" required>
                        <option value="{{ \App\Models\Test::MODE_QUESTIONS }}" selected>
                            Statisch
                        </option>
                        <option value="{{ \App\Models\Test::MODE_CATEGORIES }}">
                            Zufällige Fragen
                        </option>
                    </select>
                </div>
                <input type="submit" class="ie-submit-fix new-test-form" style="display: none;">
            </form>
        </div>
        <div class="actions">
            <button class="ui close close-modal button">Abbrechen</button>
            <button class="ui green button approve new-test-form">Test erstellen</button>
        </div>
    </div>

@stop
