@if(language() != defaultAppLanguage() && $phrase = $object->translation(defaultAppLanguage())->{$attribute})
    <div class="ui segment original-phrase">
      <p>
        <span class="float-right">{{ Emoji::getLanguageFlag(defaultAppLanguage()) }}</span>
          @if(isset($raw))
            {!! nl2br($phrase) !!}
          @else
            {{ $phrase }}
          @endif
      </p>
    </div>
@endif
