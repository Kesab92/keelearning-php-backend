@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                <th>Vorderseite</th>
                <th>Rückseite</th>
                <th>Bild-URL</th>
                <th>Oberkategorie</th>
                <th>Unterkategorie</th>
            </tr>
        </thead>
        <tbody>
        @foreach($indexCards as $indexCard)
            <tr>
                <td>
                    {{ stripControlCharacters($indexCard->front) }}
                </td>
                <td>
                    {{ stripControlCharacters($indexCard->back) }}
                </td>
                <td>
                    {{ $indexCard->cover_image_url }}
                </td>
                <td>
                    @if($indexCard->category && $indexCard->category->categorygroup)
                        {{ stripControlCharacters($indexCard->category->categorygroup->name) }}
                    @endif
                </td>
                <td>
                    @if($indexCard->category)
                        {{ stripControlCharacters($indexCard->category->name) }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
