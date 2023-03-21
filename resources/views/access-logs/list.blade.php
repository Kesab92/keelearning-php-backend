@extends('layout._layout')

@section('scripts')
    <script src="/js/vendor/medium-editor.min.js"></script>
    <script src="{{ mix('js/access-logs.js') }}"></script>
@stop

@section('main')

    <div class="content-wrapper">
        <div class="ui top attached menu">
            <div class="header item">
                Event Logs
            </div>
            <form action="/accesslogs" method="get" class="right menu user-search ui transparent icon input" style="border: 0">
                <div class="ui right aligned category item" style="min-width: 200px">
                    <select name="users[]" multiple class="user-search ui dropdown fluid">
                        <option value="">Benutzer wählen</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @if(in_array($user->id,$selectedUsers))selected @endif >{{ $user->username }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="ui attached segment">
            <div style="width: 100%; overflow-x: scroll;">
                <table class="ui selectable striped table">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th>Aktion</th>
                            <th>Datum</th>
                            <th>Meta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    @if(!$log->user_id)
                                        System
                                    @else
                                        {{ $log->user->username }} ({{ $log->user->email }})
                                    @endif
                                </td>
                                <td>
                                    {{ $log->getActionLabel() }}
                                </td>
                                <td>
                                    {{ $log->created_at->format("d.m.Y H:i:s") }}
                                </td>
                                <td>
                                    {!! $log->getMeta() !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p>Hier werden die letzten 1000 Einträge angezeigt.</p>
        </div>
    </div>

@stop
