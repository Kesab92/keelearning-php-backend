@extends('layout._csv')

@section('main')
    <table>
        <tbody>
            <tr>
                <td><strong>Prüfung:</strong></td>
                <td>{{ $test->name }}</td>
            </tr>
            <tr>
                <td><strong>User-ID:</strong></td>
                <td style="text-align:left">{{ $user->id }}</td>
            </tr>
            @if($showPersonalData)
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>
                        {{ $user->getFullName() }}
                    </td>
                </tr>
                @if($showEmails)
                    <tr>
                        <td><strong>E-Mail:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                @endif
            @endif
            <tr>
                <td><strong>Datum:</strong></td>
                <td>{{ $submission->created_at }}</td>
            </tr>
            <tr></tr>
            <tr>
                <th>
                    <strong>
                        Frage
                    </strong>
                </th>
                <th>
                    <strong>
                        Antworten
                    </strong>
                </th>
                <th>
                    <strong>
                        Richtig
                    </strong>
                </th>
                <th>
                    <strong>
                        Benutzerantwort
                    </strong>
                </th>
            </tr>
            @foreach ($submission->testSubmissionAnswers as $testSubmissionAnswer)
                <tr>
                    @if($testSubmissionAnswer->result)
                        <td style="background:green">
                    @else
                        <td style="background:red">
                    @endif
                        <strong>
                            {{ $testSubmissionAnswer->question->title }}
                        </strong>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach ($testSubmissionAnswer->question->questionAnswers as $answer)
                    <tr>
                        <td>
                            @if ($testSubmissionAnswer->question->attachments->count() >= $loop->index + 1)
                                <a href="{{ $testSubmissionAnswer->question->attachments->get($loop->index)->attachmentLink() }}">
                                    Anhang #{{ $loop->index + 1 }}
                                </a>
                            @endif
                        </td>
                        <td>
                            {{ $answer->content }}
                        </td>
                        <td>
                            @if ($answer->correct)
                                ×
                            @endif
                        </td>
                        @if (collect(explode(',', $testSubmissionAnswer->answer_ids))->contains($answer->id))
                            <td style="background-color:{{ $answer->correct ? 'green' : 'red' }};">
                                ×
                            </td>
                        @else
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@stop
