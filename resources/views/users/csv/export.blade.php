@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                <th>ID</th>
                @if ($showPersonalData)
                    <th>Username</th>
                    <th>Nachname</th>
                    <th>Vorname</th>
                @endif
                @if ($showEmails)
                    <th>E-Mail</th>
                @endif
                <th>Nutzungsbedingungen akzeptiert</th>
                <th>Aktiv</th>
                <th>Sprache</th>
                <th>Konto erstellt am</th>
                <th>Letzte Aktivität</th>
                <th>TAGs</th>
                @foreach ($metaFields as $metaField)
                    <th>{{ $metaField['label'] }}</th>
                @endforeach
                <th>Admin</th>
                <th>Löschdatum</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                @if ($showPersonalData)
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->lastname }}</td>
                    <td>{{ $user->firstname }}</td>
                @endif
                @if ($showEmails)
                    <td>{{ $user->email }}</td>
                @endif
                <td>{{ $user->tos_accepted ? 'Ja' : 'Nein' }}</td>
                <td>{{ $user->active ? 'Ja' : 'Nein' }}</td>
                <td>{{ $user->language }}</td>
                <td>{{ $user->created_at ? $user->created_at->format('d.m.Y H:i') : '' }}</td>
                <td>
                    @if($user->last_activity)
                        {{ (new \Carbon\Carbon($user->last_activity))->format('d.m.Y H:i') }}
                    @else
                        Unbekannt
                    @endif
                </td>
                <td>{{ implode(', ', $user->tags()->pluck('label')->toArray()) }}</td>
                @foreach ($metaFields as $key => $metaField)
                    <td><?php if($user->getMeta($key)):?>{{ $user->getMeta($key) }}<?php endif;?></td>
                @endforeach
                <td>{{ $user->is_admin ? 'Ja' : 'Nein' }}</td>
                <td>{{ $user->expires_at_combined ? \Carbon\Carbon::parse($user->expires_at_combined)->format('d.m.Y') : '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
