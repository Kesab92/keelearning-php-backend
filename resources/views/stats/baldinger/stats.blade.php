@foreach($days as $day=>$tagData)
    <h3>{{ $day }}</h3>
    @foreach($tagData as $tag)
        <strong>{{ $tag['label'] }}</strong><br>
        Anzahl Spieler: {{ isset($tag['users']) ? count($tag['users']) : 0 }}<br>
        Anzahl gestartete Spiele: {{ isset($tag['gamecount']) ? $tag['gamecount'] : 0 }}
        <br>
        <br>
    @endforeach
    <hr />
@endforeach
