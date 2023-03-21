<div class="ui stacked segments">
    <div class="ui segment clearfix">
        @if($competition->quizTeam)
            <h3 style="float: left;">{{ $competition->quizTeam->name }}</h3>
        @elseif($competition->tags()->count() > 0)
            @foreach($competition->tags as $tag)
                <div class="ui label">
                    {{ $tag->label }}
                </div>
            @endforeach
        @endif
        <a style="float: right;" href="/competitions/{{ $competition->id }}/refresh" class="float-right ui button basic"><i class="refresh icon"></i>Statistiken neu berechnen</a>
    </div>
    <div class="ui segment">
        <p>
            Titel: {{ $competition->title }}<br>
            Kategorie: {{ $competition->getCategoryName() }}<br>
            Zeitraum: {{ $competition->start_at->format('d.m.Y H:i') }} - {{ $competition->getEndDate()->format('d.m.Y H:i') }}
        </p>
    </div>

    <div class="ui form segment">
        <div class="field">
            <label>Beschreibung</label>
            <div class="ui segment translation-wrapper show-original">
                <textarea class="description full-editable page-content-edit">{!! $competition->description !!}</textarea>
            </div>
        </div>
    </div>

    <div class="ui segment">
        @if($competition->cover_image_url)
            <div class="item">
                <div class="content">
                    <div class="meta"><img style="width: 300px;" src="{{$competition->cover_image_url}}"></div>
                    <div class="extra"><button class="ui button red delete delete-cover-image">Cover-Bild entfernen</button></div>
                </div>
            </div>
        @else
            <button class="ui primary button cover-upload-btn">
                Cover-Bild hinterlegen
            </button>
        @endif
    </div>

    @if (!$competition->hasStartDate())
        <div class="ui segment">
            <label>Gewinnspielstart</label>
            <div class="ui calendar" id="setCompetition">
                <div class="ui input left icon">
                    <i class="calendar icon"></i>
                    <input type="text" class="start-at" name="start_at" placeholder="Gewinnspielstart" value="{{ $competition->hasStartDate() ? $competition->start_at : '' }}">
                </div>
            </div>
        </div>
    @endif
    <div class="ui segment">
        <div class="ui large menu">
            <div class="item">
                Statistiken
            </div>
            <div class="right menu">
                <div class="item">
                    <a href="/competitions/{{ $competition->id }}/download?tagIds={{ implode(',', $selectedTags) }}" target="_blank">
                        <button class="ui button basic">
                            <i class="cloud download icon" />
                            Download
                        </button>
                    </a>
                </div>
                @if ($showPersonalData)
                    <div class="item" style="min-width: 180px;max-width: calc(100% - 160px);">
                        <select name="tags[]" multiple class="tag-search ui dropdown fluid">
                            <option value="">TAGs filtern</option>
                            @foreach($tags as $tag)
                                <option
                                    value="{{ $tag->id }}"
                                    @if(in_array($tag->id,$selectedTags))
                                    selected
                                    @endif
                                >
                                    {{ $tag->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
        @if($members->count() === 0)
            <div class="ui warning message">
                <p class="content">
                    Keine Benutzer gefunden.
                </p>
            </div>
        @else
            <div class="ui info message">
                <p class="content">
                    Spiele gegen Bots werden in der Berechnung nicht berücksichtigt.
                </p>
            </div>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>Benutzer</th>
                        <th>Richtige Antworten im Zeitraum</th>
                        @if($competition->category_id === null && appId())
                            <th>Gewonnene Duelle</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($showPersonalData)
                        @foreach($members as $member)
                            <tr>
                                <td>
                                    {{ $member->getRealNameBackend($showEmails) }}
                                </td>
                                <td>{{ $member->stats['answersCorrect'] }}</td>
                                @if($competition->category_id === null)
                                    <td>{{ $member->stats['wins'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                {{ $members->count() }}
                            </td>
                            <td>{{ $members->sum('stats.answersCorrect') }}</td>
                            @if($competition->category_id === null)
                                <td>{{ $members->sum('stats.wins') }}</td>
                            @endif
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif
    </div>
    <div class="ui bottom attached menu">
        <div class="item">
            <button type="button" class="update-user ui primary button" data-competition-id="{{ $competition->id }}">
                Speichern
            </button>
        </div>
        <div class="right item">
            <form class="ui form" action="/competitions/{{ $competition->id }}/harddelete" method="POST" onclick="return confirm('Gewinnspiel unwiderruflich löschen?')">
                {{ csrf_field() }}
                <button type="submit" class="delete-user ui red button">
                    Löschen
                </button>
            </form>
        </div>
    </div>
</div>

<div class="ui modal image-cropper">
    <i class="close icon"></i>
    <div class="header">
        Coverbild hochladen
    </div>
    <div>
        <image-cropper target="/competitions/{{ $competition->id }}/upload/cover"></image-cropper>
    </div>
</div>
