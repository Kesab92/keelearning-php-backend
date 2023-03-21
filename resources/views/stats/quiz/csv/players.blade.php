@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                <th>ID</th>
                @if($showPersonalData)
                    <th>Benutzer</th>
                @endif
                @if ($showEmails)
                    <th>E-Mail</th>
                @endif
                @if($showPersonalData)
                    <th>Vorname</th>
                    <th>Nachname</th>
                @endif
                @foreach ($metaFields as $metaField)
                    <th>{{ stripControlCharacters($metaField['label']) }}</th>
                @endforeach
                <th>ToS akzeptiert?</th>
                <th>Registriert?</th>
                <th>TAGs</th>
                <th>Anzahl Spiele gesamt</th>
                <th>Spiele gewonnen</th>
                <th>Spiele unentschieden</th>
                <th>Spiele verloren</th>
                <th>Spiele abgebrochen</th>
                <th>Winrate Spiele</th>
                <th>Fragen gewusst</th>
                <th>Richtige Antworten</th>
                <th>Falsche Antworten</th>
                    @if($players->count())
                        @foreach($players->first()->stats['categories'] as $category)
                            <th>{{ stripControlCharacters($category['name']) }}</th>
                            <th>{{ stripControlCharacters($category['name']) }} richtig</th>
                            <th>{{ stripControlCharacters($category['name']) }} falsch</th>
                        @endforeach
                    @endif
                    @foreach($tags as $tagId=>$tagLabel)
                        <th>TAG: {{ stripControlCharacters($tagLabel) }}</th>
                    @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($players as $player)
                <tr>
                    <td>{{ $player->id }}</td>
                    @if($showPersonalData)
                        <td>
                            {{ stripControlCharacters($player->username) }}
                        </td>
                    @endif
                    @if ($showEmails)
                        <td>
                            {{ stripControlCharacters($player->email) }}
                        </td>
                    @endif
                    @if($showPersonalData)
                        <td>{{ stripControlCharacters($player->firstname) }}</td>
                        <td>{{ stripControlCharacters($player->lastname) }}</td>
                    @endif
                    @foreach ($metaFields as $key => $metaField)
                        <td>@if($player->getMeta($key)){{ $player->getMeta($key) }}@endif</td>
                    @endforeach
                    <td>
                      {{ $player->tos_accepted?'Ja':'Nein'}}
                    </td>
                    <td>
                      {{ !$player->isTmpAccount()?'Ja':'Nein'}}
                    </td>
                    <td>{{ $player->tags->implode('label', ',') }}</td>
                    <td>{{ $player->stats['gameCount'] }}</td>
                    <td>{{ $player->stats['gameWins'] }}</td>
                    <td>{{ $player->stats['gameDraws'] }}</td>
                    <td>{{ $player->stats['gameLosses'] }}</td>
                    <td>{{ $player->stats['gameAborts'] }}</td>
                    <td>{{ number_format($player->stats['gameWinPercentage']*100,2) }}%</td>
                    <td>{{ number_format($player->stats['answersCorrectPercentage'] * 100,2) }}%</td>
                    <td>{{ $player->stats['answersCorrect'] }}</td>
                    <td>{{ $player->stats['answersWrong'] }}</td>
                    @foreach($player->stats['categories'] as $category)
                        <td>{{ number_format($category['answersCorrectPercentage'] * 100,2) }}%</td>
                        <td>{{ $category['answersCorrect'] }}</td>
                        <td>{{ $category['answersWrong'] }}</td>
                    @endforeach
                    <?php $tagIds = $player->tags->pluck('id'); ?>
                    @foreach($tags as $tagId=>$tagLabel)
                        <td><?php if($tagIds->contains($tagId)) echo 'Ja'; ?></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
