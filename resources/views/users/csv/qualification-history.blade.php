@extends('layout._csv')

@section('main')
    <table>
        <thead>
        <tr>
            <th>Benutzer-ID</th>
            <th>Benutzer</th>
            @if ($showEmails)
                <th>E-Mail</th>
            @endif
            <th>Vorname</th>
            <th>Nachname</th>
            <th>TAGs</th>
            @foreach ($metaFields as $metaField)
                <th>{{ $metaField['label'] }}</th>
            @endforeach
            @if($hasTestHistory)
                <th>Tests bestanden</th>
            @endif
            @if($hasCourseHistory)
                <th>Kurse bestanden</th>
            @endif
            @if($hasTestHistory || $hasCourseHistory)
                <th>{{ stripControlCharacters('geschätzte Dauer Gesamt (Tests & Kurse)') }}</th>
            @endif
            @if($hasTestHistory)
                <th>Tests</th>
                <th>Status</th>
                <th>{{ stripControlCharacters('geschätzte Dauer') }}</th>
                <th>Beendet am</th>
                <th>Zertifikat</th>
            @endif
            @if($hasCourseHistory)
                <th>Kurse</th>
                <th>Status</th>
                <th>{{ stripControlCharacters('geschätzte Dauer') }}</th>
                <th>Beendet am</th>
                <th>Zertifikat</th>
            @endif
            @if(!$appSettings->getValue('hide_tag_groups'))
                @foreach($tagGroups as $tagGroup)
                    <th>TAG Gruppe: {{ $tagGroup->name }}</th>
                @endforeach
            @endif
        </tr>
        </thead>
        <tbody>
        @for($index = 0; $index < $rowCount; $index++)
            <tr>
                <td>{{ $index === 0 ? $user->id : '' }}</td>
                <td>{{ $index === 0 ? $user->username : '' }}</td>
                @if ($showEmails)
                    <td>{{ $index === 0 ? $user->email : '' }}</td>
                @endif
                <td>{{ $index === 0 ? $user->firstname : '' }}</td>
                <td>{{ $index === 0 ? $user->lastname : '' }}</td>
                <td>{{ isset($user->tags[$index]) ? stripControlCharacters($user->tags[$index]->label) : ''}}</td>
                @foreach ($metaFields as $key => $metaField)
                    <td>@if($user->getMeta($key) && $index === 0) {{ $user->getMeta($key) }} @endif</td>
                @endforeach
                @if($hasTestHistory)
                    <td>
                        {{ $index === 0 ? $user['passed_tests']->count() : '' }}
                    </td>
                @endif
                @if($hasCourseHistory)
                    <td>
                        {{ $index === 0 ? $user['passed_courses']->count() : '' }}
                    </td>
                @endif
                @if($hasTestHistory || $hasCourseHistory)
                    <td>
                        {{ $index === 0 ? $totalDuration : '' }}
                    </td>
                @endif
                @if($hasTestHistory)
                    <td>
                        {{ $qualificationHistoryForTests[$index]['title'] ?? '' }}
                    </td>
                    <td>
                        {{ $qualificationHistoryForTests[$index]['status'] ?? '' }}
                    </td>
                    <td>
                        @if(!empty($qualificationHistoryForTests[$index]['duration']))
                            {{$qualificationHistoryForTests[$index]['duration'] }}
                        @endif
                    </td>
                    <td>
                        {{ $qualificationHistoryForTests[$index]['finishedAt'] ?? '' }}
                    </td>
                    <td>
                        @if(isset($qualificationHistoryForTests[$index]['certificateLinks']))
                            {{ $qualificationHistoryForTests[$index]['certificateLinks']->implode(', ') }}
                        @endif
                    </td>
                @endif
                @if($hasCourseHistory)
                    <td>
                        {{ $qualificationHistoryForCourses[$index]['title'] ?? '' }}
                    </td>
                    <td>
                        {{ $qualificationHistoryForCourses[$index]['status'] ?? '' }}
                    </td>
                    <td>
                        @if(!empty($qualificationHistoryForCourses[$index]['duration']))
                            {{$qualificationHistoryForCourses[$index]['duration'] }}
                        @endif
                    </td>
                    <td>
                        {{ $qualificationHistoryForCourses[$index]['finishedAt'] ?? '' }}
                    </td>
                    <td>
                        @if(isset($qualificationHistoryForCourses[$index]['certificateLinks']))
                            {{ $qualificationHistoryForCourses[$index]['certificateLinks']->implode(', ') }}
                        @endif
                    </td>
                @endif
                @if(!$appSettings->getValue('hide_tag_groups'))
                    @foreach($tagGroups as $tagGroup)
                        <td>{{ $index === 0 ? stripControlCharacters($user->tags->where('tag_group_id', $tagGroup->id)->pluck('label')->implode(', ')) : '' }}</td>
                    @endforeach
                @endif
            </tr>
        @endfor
        </tbody>
    </table>
@stop
