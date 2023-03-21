@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($questions as $questionEntries)
            <tr>
                @foreach($questionEntries as $entry)
                    <td>{{ $entry }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
