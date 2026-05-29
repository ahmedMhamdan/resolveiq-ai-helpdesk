@extends('layouts.app')

@section('title', __('knowledge.title'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title" data-auto-translate>{{ __('knowledge.title') }}</h1>
        <p class="page-subtitle" data-auto-translate>{{ __('knowledge.subtitle') }}</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('knowledge.create') }}" class="btn btn-primary" data-auto-translate>
            {{ __('knowledge.new_article') }}
        </a>
    </div>
</div>

<div class="table-card kb-intro-card">
    <div class="kb-intro-head">
        <div class="kb-intro-icon">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
        <path d="M4 19.5V5.75C4 4.78 4.78 4 5.75 4H10C11.1 4 12 4.9 12 6V20C12 18.9 11.1 18 10 18H5.5C4.67 18 4 18.67 4 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M20 19.5V5.75C20 4.78 19.22 4 18.25 4H14C12.9 4 12 4.9 12 6V20C12 18.9 12.9 18 14 18H18.5C19.33 18 20 18.67 20 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</div>

        <div>
            <h2 data-auto-translate>{{ __('knowledge.what_is') }}</h2>
            <p class="page-subtitle" data-auto-translate>
                {{ __('knowledge.what_is_desc') }}
            </p>
        </div>
    </div>

    <div class="kb-intro-body">
        <p data-auto-translate>
            {{ __('knowledge.intro') }}
        </p>

        <div class="kb-feature-grid">
            <div class="kb-feature-item">
                            <div class="kb-feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4 8H20V18C20 19.1 19.1 20 18 20H6C4.9 20 4 19.1 4 18V8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M6 4H18L20 8H4L6 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9 12H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
                <div>
                    <h3 data-auto-translate>{{ __('knowledge.store_solutions') }}</h3>
                    <p data-auto-translate>{{ __('knowledge.store_solutions_desc') }}</p>
                </div>
            </div>

            <div class="kb-feature-item">
                <div class="kb-feature-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M16 19C16 16.8 14.2 15 12 15H8C5.8 15 4 16.8 4 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M10 12C12.21 12 14 10.21 14 8C14 5.79 12.21 4 10 4C7.79 4 6 5.79 6 8C6 10.21 7.79 12 10 12Z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M18 18.5C18 16.9 17.2 15.5 16 14.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M16.5 5.2C17.4 5.9 18 6.9 18 8C18 9.1 17.4 10.1 16.5 10.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
                <div>
                    <h3 data-auto-translate>{{ __('knowledge.help_agents') }}</h3>
                    <p data-auto-translate>{{ __('knowledge.help_agents_desc') }}</p>
                </div>
            </div>

            <div class="kb-feature-item">
                <div class="kb-feature-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 3L13.8 8.2L19 10L13.8 11.8L12 17L10.2 11.8L5 10L10.2 8.2L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M19 15L19.8 17.2L22 18L19.8 18.8L19 21L18.2 18.8L16 18L18.2 17.2L19 15Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
        </div>
                <div>
                    <h3 data-auto-translate>{{ __('knowledge.improve_ai') }}</h3>
                    <p data-auto-translate>{{ __('knowledge.improve_ai_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-card kb-articles-card">
    <div class="table-head kb-articles-head">
        <div>
            <h2 data-auto-translate>{{ __('knowledge.articles') }}</h2>
            <p class="page-subtitle" data-auto-translate>{{ __('knowledge.articles_subtitle') }}</p>
        </div>

        <form class="filters kb-search-form" id="kbSearchForm">
            <input
                type="search"
                id="kbSearchInput"
                placeholder="{{ __('knowledge.search_placeholder') }}"
                data-auto-translate-attribute="placeholder"
                autocomplete="off"
            >

            <button type="submit" class="kb-search-btn" data-auto-translate>{{ __('knowledge.search') }}</button>

            <button type="button" class="btn btn-secondary kb-search-reset" id="kbSearchReset" hidden data-auto-translate>
                {{ __('knowledge.reset') }}
            </button>

            <div class="kb-search-status" id="kbSearchStatus" hidden></div>
        </form>
    </div>

    <div class="table-wrap kb-articles-wrap">
        <table class="kb-articles-table">
            <thead>
                <tr>
                    <th data-auto-translate>{{ __('knowledge.article') }}</th>
                    <th data-auto-translate>{{ __('knowledge.status') }}</th>
                    <th data-auto-translate>{{ __('knowledge.author') }}</th>
                    <th data-auto-translate>{{ __('knowledge.created') }}</th>
                    <th data-auto-translate>{{ __('knowledge.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($articles as $article)
                    @php
                        $articleSearchText = Str::lower(trim(
                            $article->title . ' ' .
                            strip_tags($article->content) . ' ' .
                            $article->status . ' ' .
                            ($article->user?->name ?? __('common.system')) . ' ' .
                            $article->created_at->format('M d, Y')
                        ));
                    @endphp

                    <tr class="kb-article-row" data-search="{{ $articleSearchText }}">
                        <td data-label="{{ __('knowledge.article') }}">
                            <strong>{{ $article->title }}</strong>
                            <div class="text-muted">
                                {{ Str::limit($article->content, 80) }}
                            </div>
                        </td>

                        <td data-label="{{ __('knowledge.status') }}">
                            <span class="badge {{ $article->status === 'published' ? 'solved' : 'pending' }}">
                                {{ ucfirst($article->status) }}
                            </span>
                        </td>

                        <td data-label="{{ __('knowledge.author') }}">
                            {{ $article->user?->name ?? __('common.system') }}
                        </td>

                        <td data-label="{{ __('knowledge.created') }}">
                            {{ $article->created_at->format('M d, Y') }}
                        </td>

                        <td data-label="{{ __('knowledge.actions') }}">
                            <div class="row-actions">
                                <a href="{{ route('knowledge.edit', $article) }}" class="btn btn-sm btn-edit-soft" data-auto-translate>
                                    {{ __('knowledge.edit') }}
                                </a>

                                <form action="{{ route('knowledge.destroy', $article) }}" method="POST" onsubmit="return confirm('{{ __('tickets.confirm_delete_article') }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft" data-auto-translate>
                                        {{ __('knowledge.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="kb-empty-state">
                                <div class="kb-empty-icon">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 3H14L19 8V21H7C5.9 21 5 20.1 5 19V5C5 3.9 5.9 3 7 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M14 3V8H19" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M9 13H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M9 17H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>

                                <strong data-auto-translate>{{ __('knowledge.no_articles') }}</strong>
                                <p data-auto-translate>{{ __('knowledge.no_articles_desc') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="kb-live-empty" id="kbLiveEmpty" hidden data-auto-translate>
            {{ __('knowledge.no_matching') }}
        </div>
    </div>

    <div class="pagination">
        {{ $articles->links('vendor.pagination.resolveiq') }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const kbMessages = <?php echo json_encode([
            'no_matching' => __('knowledge.no_matching'),
            'search_applied' => __('common.search_applied'),
            'articles_found' => __('knowledge.articles_found'),
            'no_results' => __('knowledge.no_results_for'),
        ]); ?>;

        const card = document.querySelector('.kb-articles-card');
        const form = document.getElementById('kbSearchForm');
        const input = document.getElementById('kbSearchInput');
        const reset = document.getElementById('kbSearchReset');
        const status = document.getElementById('kbSearchStatus');
        const empty = document.getElementById('kbLiveEmpty');
        const rows = Array.from(document.querySelectorAll('.kb-article-row'));

        if (!card || !form || !input || !reset || !status || !empty) {
            return;
        }

        const setStatus = (message, type) => {
            status.hidden = false;
            status.textContent = message;
            status.className = `kb-search-status is-visible ${type}`;
        };

        const clearStatus = () => {
            status.hidden = true;
            status.textContent = '';
            status.className = 'kb-search-status';
        };

        const runSearch = () => {
            const query = input.value.trim().toLowerCase();
            let matchedCount = 0;

            rows.forEach((row) => {
                const text = row.dataset.search || row.textContent.toLowerCase();
                const isMatch = query === '' || text.includes(query);

                row.classList.toggle('is-hidden', !isMatch);
                row.classList.remove('is-search-match');

                if (isMatch) {
                    matchedCount++;

                    if (query !== '') {
                        row.classList.add('is-search-match');
                    }
                }
            });

            empty.hidden = !(query !== '' && matchedCount === 0);
            reset.hidden = query === '';
            card.classList.toggle('live-search-no-results', query !== '' && matchedCount === 0);

            card.classList.remove('search-pulse');
            void card.offsetWidth;
            card.classList.add('search-pulse');

            if (query === '') {
                clearStatus();
                return;
            }

            if (matchedCount > 0) {
                setStatus(`${kbMessages.search_applied} ${matchedCount} ${kbMessages.articles_found}`, 'is-success');
            } else {
                setStatus(`{{ __('knowledge.no_results_for') }} "${query}".`, 'is-warning');
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            runSearch();
        });

        reset.addEventListener('click', () => {
            input.value = '';

            rows.forEach((row) => {
                row.classList.remove('is-hidden', 'is-search-match');
            });

            empty.hidden = true;
            reset.hidden = true;
            card.classList.remove('live-search-no-results', 'search-pulse');
            clearStatus();
            input.focus();
        });
    });
</script>
@endsection