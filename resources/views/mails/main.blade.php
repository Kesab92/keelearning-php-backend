@extends('layout._layout')

@section('scripts')
    <script src="{{ mix('js/mails.js') }}"></script>
@stop

@section('main')
    <div class="content-wrapper">
        <h2 class="ui header">
            Mail-Templates verwalten
        </h2>
        <div class="ui attached segment mails-wrapper">
            <div class="ui divided relaxed spaced selection list mail-list">
                @foreach($mails as $mail)
                    <div class="item selection mail @if(!$mail->isTranslated())translation-missing @endif" data-mail-type="{{ $mail->type }}">
                        <div class="content">
                            <div class="header">
                                <div class="ui horizontal label">
                                    {{ $mail->type }}
                                </div>
                                {{ $mail->setLanguage(language())->title }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mail-content ui basic segment">
            </div>
        </div>
    </div>
@stop
