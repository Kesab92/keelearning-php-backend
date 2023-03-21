@extends('layout._layout')

@section('scripts')
    @if(isSuperAdmin())
        <link rel="stylesheet" href="//cdn.jsdelivr.net/medium-editor/latest/css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8">
        <link rel="stylesheet" href="/css/vendor/medium-editor-insert-plugin.min.css" type="text/css" media="screen" charset="utf-8">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" media="screen" charset="utf-8">
        <script src="/js/vendor/medium-editor.min.js"></script>
        <script src="/js/vendor/handlebars.min.js"></script>
        <script src="/js/vendor/jquery.ui.widget.js"></script>
        <script src="/js/vendor/jquery.iframe-transport.js"></script>
        <script src="/js/vendor/jquery.fileupload.js"></script>
        <script src="/js/vendor/medium-editor-insert-plugin.min.js"></script>
        <script src="{{ mix('js/faq.js') }}"></script>
    @endif
@stop

@section('main')

    <div class="content-wrapper">
        <div class="ui tabular menu" style="margin-bottom:0">
            @foreach($pages as $i => $page)
                <div class="{{ $i == 0?'active':'' }} item" data-tab="tab-help-{{ $page->id }}">
                    <span class="title">
                        {{ $page->title }}
                    </span>
                    @if(isSuperAdmin())
                        <i class="fa fa-pencil edit-faq-title" style="margin-left: 10px" aria-hidden="true" data-page-id="{{ $page->id }}"></i>
                    @endif
                </div>
            @endforeach
            @if(isSuperAdmin())
                <div class="item add-faq-page">
                    <i class="plus square outline icon"></i>
                </div>
            @endif
        </div>
        @foreach($pages as $i => $page)
            <div class="{{ $i == 0?'active':'' }} ui tab segment pages-wrapper" data-tab="tab-help-{{ $page->id }}" style="margin-top:0">
                <div class="full-editable faq-content" style="margin-bottom: 20px;">
                    {!! $page->content !!}
                </div>
                @if(isSuperAdmin())
                    <div class="ui bottom attached menu">
                        <div class="item">
                            <button class="ui primary button save-faq-changes" data-page-id="{{ $page->id }}">Änderungen speichern</button>
                        </div>
                        <form action="/misc/faq/{{ $page->id }}/remove" method="POST" class="item right">
                            {{ csrf_field() }}
                            <button type="submit" class="ui red button delete-faq-page">Seite löschen</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@stop
