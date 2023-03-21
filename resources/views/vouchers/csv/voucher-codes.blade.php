@extends('layout._csv')

@section('main')
    <h3>Noch nicht eingelöste Voucher</h3>
    <table class="ui selectable striped table sortable">
        <thead>
            <tr>
                <th>Voucher Code</th>
                <th>Erstellungsdatum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unusedCodes as $voucherCode)
                <tr>
                    <td>
                        {{ $voucherCode->code }}
                    </td>
                    <td>
                        {{ $voucherCode->created_at->format('d.m.Y H:i') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($usedCodes) > 0)
        <h3>Bereits eingelöste Voucher</h3>
        <table class="ui selectable striped table sortable">
            <thead>
                <tr>
                    <th>Voucher Code</th>
                    <th>Einlösungsdatum</th>
                    @if ($showPersonalData)
                        <th>Benutzer</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($usedCodes as $voucherCode)
                    <tr>
                        <td>
                            {{ $voucherCode->code }}
                        </td>
                        <td>
                            {{ $voucherCode->cash_in_date->format('d.m.Y H:i') }}
                        </td>
                        @if ($showPersonalData)
                            <td>
                                @if($voucherCode->user)
                                    {{ $voucherCode->user->getDisplayNameBackend($showEmails) }}
                                @else
                                    Gelöschter Benutzer
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@stop
