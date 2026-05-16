@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Knowledge Base</h1>
        <p class="page-subtitle">Manage reusable support articles and internal help content.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('knowledge.create') }}" class="btn btn-primary">
            + New Article
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
            <h2>What is the Knowledge Base?</h2>
            <p class="page-subtitle">
                The Knowledge Base is a library of ready support solutions that agents and admins can reuse while handling tickets.
            </p>
        </div>
    </div>

    <div class="kb-intro-body">
        <p>
            Instead of writing the same answer many times, we save common solutions here,
            such as password reset steps, login problems, account verification, or troubleshooting instructions.
            Later, the AI Assistant can use published articles from this library to generate more accurate replies
            based on real helpdesk content.
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
                    <h3>1. Store solutions</h3>
                    <p>Create short articles for repeated customer problems.</p>
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
                    <h3>2. Help agents</h3>
                    <p>Agents can quickly review the correct steps before replying.</p>
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
                    <h3>3. Improve AI replies</h3>
                    <p>Published articles can be sent as extra context to the AI Assistant.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-head">
        <div>
            <h2>Articles</h2>
            <p class="page-subtitle">Reusable support content for agents and AI-assisted replies.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Status</th>
                    <th>Author</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <td>
                            <strong>{{ $article->title }}</strong>
                            <div class="text-muted">
                                {{ Str::limit($article->content, 80) }}
                            </div>
                        </td>

                        <td>
                            <span class="badge {{ $article->status === 'published' ? 'solved' : 'pending' }}">
                                {{ ucfirst($article->status) }}
                            </span>
                        </td>

                        <td>{{ $article->user?->name ?? 'System' }}</td>

                        <td>{{ $article->created_at->format('M d, Y') }}</td>

                        <td>
                            <div class="row-actions">
                                <a href="{{ route('knowledge.edit', $article) }}" class="btn btn-sm btn-edit-soft">
                                    Edit
                                </a>

                                <form action="{{ route('knowledge.destroy', $article) }}" method="POST" onsubmit="return confirm('Delete this article?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        Delete
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
                                <strong>No articles found yet.</strong>
                                <p>Create your first article to start building the support knowledge library.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $articles->links('vendor.pagination.resolveiq') }}
    </div>
</div>
@endsection
