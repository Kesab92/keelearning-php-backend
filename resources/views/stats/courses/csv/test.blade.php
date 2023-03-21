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
            @endif
            <th>Status</th>
            <th>Bestanden am</th>
            <th>Gescheitert am</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                @if ($showPersonalData)
                    <td>{{ $user['username'] }}</td>
                    <td>{{ $user['lastname'] }}</td>
                    <td>{{ $user['firstname'] }}</td>
                    @if ($showEmails)
                        <td>{{ $user['email'] }}</td>
                    @endif
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
                    @if($user['passed'] === 1 && $user['finished_at'])
                        {{ $user['finished_at']->format('Y-m-d H:i:s') }}
                    @endif
                </td>
                <td>
                    @if($user['passed'] === 0 && $user['finished_at'])
                        {{ $user['finished_at']->format('Y-m-d H:i:s') }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
