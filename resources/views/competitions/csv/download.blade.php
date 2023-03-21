@extends('layout._csv')

@section('main')
    <table class="ui celled table">
        <thead>
        <tr>
            <th>Benutzer</th>
            <th>Richtige Antworten im Zeitraum</th>
            @if($competition->category_id === null)
                <th>Gewonnene Duelle</th>
            @endif
            @if ($showPersonalData)
                @foreach($tagGroups as $tagGroup)
                    <th>TAG Gruppe: {{ $tagGroup->name }}</th>
                @endforeach
                @foreach($tags as $tag)
                    <th>TAG: {{ $tag->label }}</th>
                @endforeach
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
                    @foreach($tagGroups as $tagGroup)
                        <td>{{ $member->tags->where('tag_group_id', $tagGroup->id)->pluck('label')->implode(', ') }}</td>
                    @endforeach
                    @foreach($tags as $tag)
                        <td><?php if($member->tags->contains('id', $tag->id)) echo 'Ja'; ?></td>
                    @endforeach
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
@stop
