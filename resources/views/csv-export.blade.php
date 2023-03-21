@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>
                        {{ $cell }}
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
