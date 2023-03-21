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
            @foreach($formFields->sortBy('position')->values() as $field)
                <th>{{ $field->getFormattedTitle() }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($answers as $answer)
            <tr>
                @if ($showPersonalData)
                    <td>{{ $answer['user']['username'] }}</td>
                    <td>{{ $answer['user']['lastname'] }}</td>
                    <td>{{ $answer['user']['firstname'] }}</td>
                    @if ($showEmails)
                        <td>{{ $answer['user']['email'] }}</td>
                    @endif
                @endif
                @foreach($formFields->sortBy('position')->values() as $field)
                    <td>
                        @if($answer['fields']->has($field->id))
                            {{ $answer['fields']->get($field->id)->getFormattedAnswer() }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
