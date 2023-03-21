@extends('layout._layout')

@section('scripts')
    <script src="{{ mix('js/stats.js') }}"></script>
@stop

@section('main')
    <div class="content-wrapper stats-wrapper">
        <div class="ui top attached tabular menu">
            <div class="item active" style="cursor: pointer;">
                Aufrufe
            </div>
            <div class="right menu">
                <div class="item">
                    <a href="/stats/views/csv" target="_blank" class="csv-download-button ui labeled icon button">
                        <i class="cloud download icon"></i>
                        Excel Download
                    </a>
                </div>
            </div>
        </div>
        <div class="active ui bottom attached tab segment">
            <table class="ui selectable striped table sortable table-player-stats">
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
                                {{ $entry['created_at'] ? $entry['created_at']->format('d.m.Y H:i') : 'n/a' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop
