@extends('layout._csv')

@section('main')
    <table class="ui selectable striped table sortable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Frage</th>
                <th>Fragetyp</th>
                @if($appSettings->getValue('use_subcategory_system'))
                    <th>Oberkategorie</th>
                @endif
                <th>Kategorie</th>
                <th>Richtig beantwortet</th>
                <th>Falsch beantwortet</th>
                <th>Schwierigkeit (kleine Wert = schwierig)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($questions as $question)
                <tr>
                    <td>
                        {{ $question->id }}
                    </td>
                    <td>
                        <?php // The preg replace is needed, because phpoffice doesn't like special characters in the html file used to generate the xlsx ?>
                        {{ stripControlCharacters($question->title) }}
                    </td>
                    <td>
                        {{ $question->getTypeLabel() }}
                    </td>
                    @if($appSettings->getValue('use_subcategory_system'))
                        <td>
                            @if($question->category && $question->category->categorygroup)
                                <?php // The preg replace is needed, because phpoffice doesn't like special characters in the html file used to generate the xlsx ?>
                                {{ stripControlCharacters($question->category->categorygroup->name) }}
                            @endif
                        </td>                    @endif
                    <td>
                    @if($question->category)
                        <?php // The preg replace is needed, because phpoffice doesn't like special characters in the html file used to generate the xlsx ?>
                        {{ stripControlCharacters($question->category->name) }}
                    @endif
                    </td>
                    <td>
                        {{ $question->stats['correct'] }}
                    </td>
                    <td>
                        {{ $question->stats['wrong'] }}
                    </td>
                    <td>
                        {{ $question->difficulty > 0 ? '+':'' }}{{ round($question->difficulty * 100) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
