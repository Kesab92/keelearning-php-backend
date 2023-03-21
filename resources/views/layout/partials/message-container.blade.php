@if(Session::has('error-message') || Session::has('success-message'))
    <div class="content-wrapper message-container">
        <div class="ui attached">

            @if(Session::has('error-message'))
                <div class="ui negative message">
                    {!! Session::get('error-message') !!}
                </div>
            @endif
            @if(Session::has('success-message'))
                <div class="ui positive message">
                    {!! Session::get('success-message') !!}
                </div>
            @endif

        </div>
    </div>
@endif

@if (Session::has('lang-message'))
<div class="ui positive message" style="position: fixed; z-index: 20; top: 50px; right: 50px;padding-right: 30px;">
    <i class="close icon"></i>
    <p class="header">
        {!! Session::get('lang-message') !!}
    </p>
</div>
@endif
