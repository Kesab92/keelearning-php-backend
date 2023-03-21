<div class="ui stacked segments @if(language() != defaultAppLanguage()) show-original-translation @endif">
    <div class="ui segment translation-wrapper">
        <input class="mail-title" value="{{ $mail->setLanguage(language())->getRawTranslation('title') }}">
        @include('partials.originalPhrase', ['object' => $mail, 'attribute' => 'title'])
    </div>
    <div class="ui segment translation-wrapper">
        <textarea class="mail-content-edit">{!! htmlspecialchars($mail->setLanguage(language())->getRawTranslation('body')) !!}</textarea>
        @include('partials.originalPhrase', ['object' => $mail, 'attribute' => 'body', 'raw' => true])
    </div>
    @if (\Lang::has('mail_settings.info.' . $mail->type))
        <div class="ui segment info message">
            {{ __('mail_settings.info.' . $mail->type) }}
        </div>
    @endif
    <div class="ui segment">
        <strong>Platzhalter:</strong><br>
        @foreach($tags as $tag)
            %{{ $tag }}%<br>
        @endforeach
    </div>
    <div class="ui bottom attached menu">
        <div class="item">
            {{ Emoji::getLanguageFlag(language()) }}
            <input type="hidden" name="lang" value="{{ language() }}">
        </div>
        @if($mail->app_id || language() == defaultAppLanguage())
            <div class="item">
                <button data-mail-type="{{ $mail->type }}" class="save-button ui primary button">
                    Speichern
                </button>
            </div>
        @else
            <div class="item">
                <button class="save-button ui primary button disabled">
                    Speichern
                </button>
            </div>
            <div class="item">
                <p>
                    Zuerst in Standardsprache {{ Emoji::getLanguageFlag(defaultAppLanguage()) }} anpassen
                </p>
            </div>
        @endif
    </div>
</div>
