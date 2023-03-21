@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                @if($showPersonalData)
                    <th>Benutzername</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    @if($showEmails)
                        <th>Email</th>
                    @endif
                @endif
                @foreach($tagGroups as $tagGroup)
                    <th>TAG Gruppe: {{ $tagGroup->name }}</th>
                @endforeach
                <th>Test gestartet</th>
                <th>Ergebnis</th>
                <th>Prozent richtig</th>
                @if($test->minutes)
                    <th>Vorhergesehene Dauer</th>
                @endif
                @if(appId() === \App\Models\App::ID_MONEYCOASTER)
                    <th>Antwortzeit (Sekunden) für die letzten 11 Fragen</th>
                @endif
                @foreach ($test->testQuestions as $testQuestion)
                    <th>{{ $testQuestion->question->title }}</th>
                @endforeach
                <th></th>
                @foreach($tags as $tag)
                    <th>TAG: {{ $tag->label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr>
                    @if($showPersonalData)
                        <td>
                            {{ $submission->user->username }}
                        </td>
                        <td>
                            {{ $submission->user->firstname }}
                        </td>
                        <td>
                            {{ $submission->user->lastname }}
                        </td>
                        @if($showEmails)
                            <td>
                                {{ $submission->user->email }}
                            </td>
                        @endif
                    @endif
                    @foreach($tagGroups as $tagGroup)
                        <td>{{ $submission->user->tags->where('tag_group_id', $tagGroup->id)->pluck('label')->implode(', ') }}</td>
                    @endforeach
                    <td>
                        {{ $submission->created_at }}
                    </td>
                    <td>
                        {{ $submission->result ? 'Bestanden' : 'Nicht bestanden' }}
                    </td>
                    <td>
                        {{ $submission->percentage() }}
                    </td>
                    @if($test->minutes)
                        <td>
                            {{ $test->minutes }} Minute{{ $test->minutes > 1 ? 'n' : ''}}
                        </td>
                    @endif
                    @if(appId() === \App\Models\App::ID_MONEYCOASTER)
                        <td>{{ $submission->moneycoasterAnswertime() }}</td>
                    @endif
                    @foreach ($test->testQuestions as $testQuestion)
                        @foreach ($submission->testSubmissionAnswers as $answer)
                            @if ($testQuestion->id === $answer->test_question_id)
                                <td>{{ $answer->result ? 'richtig' : 'falsch' }}</td>
                            @endif
                        @endforeach
                    @endforeach
                    <td></td>
                    @foreach($tags as $tag)
                        <td><?php if($submission->user->tags->contains('id', $tag->id)) echo 'x'; ?></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
