Benutzer #{{ $meta['user_id'] }}@if($user), {{ $user->getDisplayNameBackend() }} @else, Benutzer gelöscht @endif<br>
@if(isset($meta['user_updates']))
    @foreach($meta['user_updates'] as $key => $value)
        {{ $key }}:
        @if($key !== 'password')
            @if(!is_string($value)) {{ json_encode($value) }} @else {{ $value }} @endif
        @endif
        <br>
    @endforeach
@endif
@if(isset($meta['tag_updates']))
    @foreach($meta['tag_updates'] as $key => $value)
        TAGs ({{ $key }}): {{ implode(', ', $value) }}<br>
    @endforeach
@endif
@if(isset($meta['permission_updates']))
    @foreach($meta['permission_updates'] as $key => $value)
        Berechtigungen ({{ $key }}): {{ implode(', ', $value) }}<br>
    @endforeach
@endif
