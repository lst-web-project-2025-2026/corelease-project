@if ($paginator->hasPages())
    <nav class="pagination-container">
        {{-- First Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" title="First Page">&laquo;&laquo;</span>
        @else
            <a href="{{ $paginator->url(1) }}" class="pagination-btn" title="First Page">&laquo;&laquo;</a>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" title="Previous Page">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" title="Previous Page">&laquo;</a>
        @endif

        {{-- Pagination Elements --}}
        <div class="pagination-numbers">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-number active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-number">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next" title="Next Page">&raquo;</a>
        @else
            <span class="pagination-btn disabled" title="Next Page">&raquo;</span>
        @endif

        {{-- Last Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pagination-btn" title="Last Page">&raquo;&raquo;</a>
        @else
            <span class="pagination-btn disabled" title="Last Page">&raquo;&raquo;</span>
        @endif
    </nav>
@endif
