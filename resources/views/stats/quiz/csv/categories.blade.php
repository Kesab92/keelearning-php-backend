@extends('layout._csv')

@section('main')
    <table class="ui selectable striped table sortable">
        <thead>
            <tr>
                @if($appSettings->getValue('use_subcategory_system'))
                    <th>Oberkategorie</th>
                @endif
                <th>Kategorie</th>
                <th>Richtig beantwortete Fragen</th>
                <th>Falsch beantwortete Fragen</th>
                <th>Aktiv</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    @if($appSettings->getValue('use_subcategory_system'))
                        <td>
                            @if($category->categorygroup)
                                {{-- The preg replace is needed, because phpoffice doesn't like special characters in the html file used to generate the xlsx --}}
                                {{ stripControlCharacters($category->categorygroup->name) }}
                            @endif
                        </td>
                    @endif
                    <td>
                        <?php // The preg replace is needed, because phpoffice doesn't like special characters in the html file used to generate the xlsx ?>
                        {{ stripControlCharacters($category->name) }}
                    </td>
                    <td>
                        {{ $category->stats['correct'] }}
                    </td>
                    <td>
                        {{ $category->stats['wrong'] }}
                    </td>
                    <td>
                        {{ $category->active ? 'X':''}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
