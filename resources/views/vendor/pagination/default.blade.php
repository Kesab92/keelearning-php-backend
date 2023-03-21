@if ($paginator->hasPages())
    <div class="ui pagination menu">
        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <div class="disabled item">
                    {{ $element }}
                </div>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <a class="active item">
                            {{ $page }}
                        </a>
                    @else
                        <a href="{{ $url }}" class="item js-no-relaunch-forwarding">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
@endif
