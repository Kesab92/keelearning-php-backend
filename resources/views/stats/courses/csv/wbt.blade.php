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
            <th>Datum</th>
            <th>Titel</th>
            <th>Dauer</th>
            <th>Ergebnis</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($events as $event)
            <tr>
                @if ($showPersonalData)
                    <?php
                        $user = $users->get($event['user_id']);
                    ?>
                    <td>{{ $event['user'] }}</td>
                    <td>@if($user){{ $user['lastname'] }}@endif</td>
                    <td>@if($user){{ $user['firstname'] }}@endif</td>
                    @if ($showEmails)
                        <td>@if($user){{ $user['email'] }}@endif</td>
                    @endif
                @endif
                <td>
                    {{ $event['date'] }}
                </td>
                <td>
                    {{ $event['title'] }}
                </td>
                <td>
                    {{ $event['duration'] }}
                </td>
                <td>
                    {{ $event['score'] }}
                </td>
                <td>
                    {{ $event['status'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
