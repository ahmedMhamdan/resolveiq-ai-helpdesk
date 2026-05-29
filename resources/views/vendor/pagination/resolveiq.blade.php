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

    @once
        <style>
            .live-search-no-results .riq-pagination,
            .riq-pagination.is-live-hidden {
                display: none !important;
            }
        </style>

        <script>
            (function () {
                if (window.__resolveIqPaginationLiveSearchFix) {
                    return;
                }

                window.__resolveIqPaginationLiveSearchFix = true;

                const paginationSelector = '.riq-pagination';
                const cardSelector = [
                    '.dashboard-activity-card',
                    '.deleted-tickets-card',
                    '.unassigned-table-card',
                    '.tickets-index-card',
                    '.table-card',
                    '.card'
                ].join(', ');

                const rowSelector = [
                    '.activity-item',
                    '.deleted-ticket-row',
                    '.unassigned-ticket-row',
                    '.live-ticket-row',
                    '.overdue-ticket-row',
                    '.ticket-row',
                    '.tickets-table tbody tr',
                    '.deleted-tickets-table tbody tr',
                    '.unassigned-table tbody tr',
                    '.overdue-table tbody tr',
                    'table tbody tr'
                ].join(', ');

                const inputSelector = [
                    '.activity-search-form input',
                    '.deleted-ticket-search input',
                    '.unassigned-ticket-search input',
                    '.overdue-ticket-search input',
                    '.overdue-search-form input',
                    '.filters input[name="search"]',
                    '.filters input[type="search"]',
                    '.filters input[type="text"]'
                ].join(', ');

                const emptySelector = [
                    '.activity-live-empty',
                    '.live-search-empty'
                ].join(', ');

                function isVisible(element) {
                    if (!element || element.hidden) {
                        return false;
                    }

                    const style = window.getComputedStyle(element);

                    return style.display !== 'none'
                        && style.visibility !== 'hidden'
                        && style.opacity !== '0';
                }

                function hasActiveLiveSearch(card) {
                    return Array.from(card.querySelectorAll(inputSelector))
                        .some((input) => input.value && input.value.trim() !== '');
                }

                function hasVisibleLiveEmpty(card) {
                    return Array.from(card.querySelectorAll(emptySelector))
                        .some((empty) => isVisible(empty));
                }

                function isDataRow(row) {
                    return !row.querySelector('td[colspan], th[colspan]');
                }

                function getSearchRows(card) {
                    return Array.from(card.querySelectorAll(rowSelector))
                        .filter((row) => !row.closest(paginationSelector))
                        .filter(isDataRow);
                }

                function hasVisibleSearchRows(card) {
                    return getSearchRows(card).some((row) => {
                        return isVisible(row) && !row.classList.contains('is-hidden');
                    });
                }

                function updatePagination(pagination) {
                    const card = pagination.closest(cardSelector) || pagination.parentElement;

                    if (!card) {
                        return;
                    }

                    const hasNoResultsClass = card.classList.contains('live-search-no-results');
                    const activeSearch = hasActiveLiveSearch(card);
                    const rows = getSearchRows(card);
                    const noVisibleRows = rows.length > 0 && !hasVisibleSearchRows(card);
                    const hasLiveEmpty = hasVisibleLiveEmpty(card);

                    pagination.classList.toggle(
                        'is-live-hidden',
                        hasNoResultsClass || hasLiveEmpty || (activeSearch && noVisibleRows)
                    );
                }

                function refreshPaginations() {
                    document.querySelectorAll(paginationSelector).forEach(updatePagination);
                }

                function bootPaginationFix() {
                    refreshPaginations();

                    document.addEventListener('input', (event) => {
                        if (event.target.matches(inputSelector)) {
                            window.requestAnimationFrame(refreshPaginations);
                        }
                    });

                    document.addEventListener('click', () => {
                        window.setTimeout(refreshPaginations, 0);
                    });

                    const observer = new MutationObserver(() => {
                        window.requestAnimationFrame(refreshPaginations);
                    });

                    observer.observe(document.body, {
                        subtree: true,
                        childList: true,
                        attributes: true,
                        attributeFilter: ['class', 'hidden', 'style']
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bootPaginationFix);
                } else {
                    bootPaginationFix();
                }
            })();
        </script>
    @endonce

@endif
