@if ($paginator->hasPages())
    <nav class="riq-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="riq-pagination-info">
            <span data-auto-translate>Showing</span>
            <strong>{{ $paginator->firstItem() }}</strong>
            <span data-auto-translate>to</span>
            <strong>{{ $paginator->lastItem() }}</strong>
            <span data-auto-translate>of</span>
            <strong>{{ $paginator->total() }}</strong>
            <span data-auto-translate>results</span>
        </div>

        <div class="riq-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="riq-page-btn disabled" aria-disabled="true">‹</span>
            @else
                <a class="riq-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="riq-page-btn dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="riq-page-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="riq-page-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="riq-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>
            @else
                <span class="riq-page-btn disabled" aria-disabled="true">›</span>
            @endif
        </div>
    </nav>
@endif
