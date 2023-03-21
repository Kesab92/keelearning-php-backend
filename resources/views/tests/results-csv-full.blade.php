@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                <th>
                    @if ($showPersonalData)
                        Benutzername
                    @else
                        Benutzer-ID
                    @endif
                </th>
                <th>Bestanden</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>
                        @if ($showPersonalData)
                            {{ $user['username'] }}
                        @else
                            {{ $user['id'] }}
                        @endif
                    </td>
                    <td>{{ $user['passed'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
