@extends('layout._csv')

@section('main')
    <table class="ui selectable striped table sortable">
        <thead>
            <tr>
                @foreach($fields as $field)
                    <th>{{ $field }}</th>
                @endforeach
            </tr>
        </thead>
    </table>
@stop