@extends('layout._csv')

@section('main')
    <table>
        <thead>
            <tr>
                @if ($showPersonalData)
                <th>
                    Benutzer
                </th>
                @endif
                @if ($showIp)
                    <th>
                        Land
                    </th>
                @endif
                @if ($showEmails && $showPersonalData)
                    <th>
                        E-Mail
                    </th>
                @endif
                <th>
                    TAGs
                </th>
                @for ($i = 1; $i <= $boxCount; $i++)
                    <th>
                        Alle Kategorien - Box #{{ $i }}
                    </th>
                @endfor
                @if ($players->count())
                    @foreach ($players->first()->stats['categories'] as $category)
                        @for ($i = 1; $i <= $boxCount; $i++)
                            <th>
                                {{ $category['name'] }} - Box #{{ $i }}
                            </th>
                        @endfor
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($players as $player)
                <tr>
                    @if ($showPersonalData)
                        <td>
                            {{ $player->username }}
                        </td>
                    @endif
                    @if ($showIp)
                        <td>
                          {{ $player->country }}
                        </td>
                    @endif
                    @if ($showEmails && $showPersonalData)
                        <td>
                            {{ $player->email }}
                        </td>
                    @endif
                    <td>
                        {{ $player->tags->implode('label', ',') }}
                    </td>
                    @for ($i = 1; $i <= $boxCount; $i++)
                        <td>
                            {{ $player->stats['all']['box_' . $i . '_total'] }}
                        </td>
                    @endfor
                    @foreach ($player->stats['categories'] as $category)
                        @for ($i = 1; $i <= $boxCount; $i++)
                            <td>
                                {{ $category['box_' . $i . '_total'] }}
                            </td>
                        @endfor
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
