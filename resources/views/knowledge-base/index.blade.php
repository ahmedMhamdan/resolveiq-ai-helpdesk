@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Knowledge Base</h1>
        <p class="page-subtitle">Manage support articles and internal help content.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('knowledge.create') }}" class="btn btn-primary">
            + New Article
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-head">
        <div>
            <h2>Articles</h2>
            <p class="page-subtitle">Reusable support content for agents.</p>
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
                        <td colspan="5">No articles found.</td>
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
