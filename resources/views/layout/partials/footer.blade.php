<footer class="c-offeredByKeeunit">
    Ein Angebot von <a href="http://keeunit.de/" target="_blank">keeunit</a>
</footer>
@if(defaultAppLanguage() !== language())
    <div class="secondaryLanguage__container">
        <div class="secondaryLanguage__label">{{ __('general.lang_' . language()) }}</div>
    </div>
@endif
