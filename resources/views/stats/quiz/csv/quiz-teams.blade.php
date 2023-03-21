@extends('layout._csv')

@section('main')
    <table class="ui selectable striped table sortable">
        <thead>
            <tr>
                <th>Quiz-Team</th>
                <th>Richtig beantwortete Fragen</th>
                <th>Gewonnene Spiele</th>
                <th>Mitglieder</th>
                <th>Erstellt am</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quizTeams as $quizTeam)
                <tr>
                    <td>
                        {{ $quizTeam->name }}
                    </td>
                    <td>
                        {{ $quizTeam->stats['answersCorrect'] }}
                    </td>
                    <td>
                        {{ round($quizTeam->stats['gameWinPercentage'], 2) }}%
                    </td>
                    <td>
                        {{ $quizTeam->member_count }}
                    </td>
                    <td>
                        {{ $quizTeam->created_at ? $quizTeam->created_at->format('d.m.Y') : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
