@extends('layout._csv')

@section('main')
    <table>
        <thead>
        <tr>
            @if($showPersonalData)
                <th>Benutzername</th>
                @if($showEmails)
                    <th>Email</th>
                @endif
            @endif
            <th>Datum</th>
            <th>Ereignis</th>
            <th>Bestanden</th>
            @foreach($tagGroups as $tagGroup)
                <th>TAG Gruppe: {{ $tagGroup->name }}</th>
            @endforeach
            @foreach($tags as $tag)
                <th>TAG: {{ $tag->label }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            @foreach ($user['history'] as $entry)
                <tr>
                    @if($showPersonalData)
                        <td>{{ $user['username']}}</td>
                        @if($showEmails)
                            <td>{{ $user['email']}}</td>
                        @endif
                    @endif
                    <td>{{ $entry['date'] }}</td>
                    <td>{{ $entry['type'] }}</td>
                    @if (!empty($entry['meta']) && !empty($entry['meta']['result']))
                        <td>{{ $entry['meta']['result'] }}</td>
                    @else
                        <td></td>
                    @endif
                    @foreach($tagGroups as $tagGroup)
                        <td>{{ $user['tags']->where('tag_group_id', $tagGroup->id)->pluck('label')->implode(', ') }}</td>
                    @endforeach
                    @foreach($tags as $tag)
                        <td><?php if($user['tags']->contains('id', $tag->id)) echo 'x'; ?></td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
@stop
