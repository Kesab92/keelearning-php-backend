@extends('layout._csv')

@section('main')
    <table>
        <thead>
        <tr>
            @if ($showPersonalData)
                <th>Username</th>
                <th>Nachname</th>
                <th>Vorname</th>
                @if ($showEmails)
                    <th>E-Mail</th>
                @endif
                <th>User Aktiv</th>
            @endif
            <th>Kurs-Status</th>
            <th>Bestanden am</th>
            <th>Gescheitert am</th>
            <th>Inhalte erfolgreich bearbeitet</th>
            <th>Lernzeit</th>
            @foreach($course->chapters as $chapter)
                @foreach($chapter->contents->where('visible', 1) as $content)
                    <th>{{ stripControlCharacters($chapter->title) }}: {{ $content->title ? stripControlCharacters($content->title) : (($content->canHaveForeignObject() && $content->relatable) ? stripControlCharacters($content->relatable->title) : '') }}</th>
                @endforeach
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                @if ($showPersonalData)
                    <td>{{ stripControlCharacters($user['username']) }}</td>
                    <td>{{ stripControlCharacters($user['lastname']) }}</td>
                    <td>{{ stripControlCharacters($user['firstname']) }}</td>
                    @if ($showEmails)
                        <td>{{ stripControlCharacters($user['email']) }}</td>
                    @endif
                    <td>{{ $user['active'] ? 'Ja' : 'Nein' }}</td>
                @endif
                <td>
                    @if($user['passed'] === null)
                        Noch nicht abgeschlossen
                    @else
                        @if($user['passed'])
                            Bestanden
                        @else
                            Gescheitert
                        @endif
                    @endif
                </td>
                <td>
                    @if($user['passed'] === 1)
                        {{ $user['finished_at']->format('Y-m-d H:i:s') }}
                    @endif
                </td>
                <td>
                    @if($user['passed'] === 0)
                        {{ $user['finished_at']->format('Y-m-d H:i:s') }}
                    @endif
                </td>
                <td>
                    {{ $user['passedCount'] }} / {{ $contentCount }}
                </td>
                <td>
                    {{ $user['total_minutes'] }} Minute{{ $user['total_minutes'] > 1 ? 'n' : ''}}
                </td>
                @foreach($course->chapters as $chapter)
                    @foreach($chapter->contents->where('visible', 1) as $content)
                        <td>
                        @if($user['content-' . $content->id] !== null)
                            {{ $user['content-' . $content->id] ? 'Bestanden' : '(Noch) nicht bestanden' }}
                        @else
                            -
                        @endif
                        </td>
                    @endforeach
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
