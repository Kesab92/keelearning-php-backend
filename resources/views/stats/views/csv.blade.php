@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                <th>
                    Content
                </th>
                <th>
                    Views
                </th>
                <th>
                    Seit
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>
                        {{ $entry['title'] }}
                    </td>
                    <td>
                        {{ $entry['views'] ?: 'n/a' }}
                    </td>
                    <td>
                        {{ $entry['created_at'] ? $entry['created_at']->toDateTimeString() : 'n/a' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
