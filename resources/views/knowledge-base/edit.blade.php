@extends('layouts.app')

@section('title', 'Edit Article')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Article</h1>
        <p class="page-subtitle">Update support knowledge content used by agents and AI-assisted replies.</p>
    </div>

    <a href="{{ route('knowledge.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card kb-form-note-card">
    <div class="table-head">
        <div>
            <h2>Before updating</h2>
            <p class="page-subtitle">Make sure the article is accurate before publishing it.</p>
        </div>
    </div>

    <div class="kb-form-note-body">
        <p class="text-muted">
            Published articles may be used later as extra context for the AI Assistant. Keep the steps clear and avoid
            outdated instructions. Use Draft if the article still needs review.
        </p>
    </div>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $article->title }}</h2>
            <p class="page-subtitle">Edit this article below.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('knowledge.update', $article) }}" method="POST" class="ticket-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="title">Article Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $article->title) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
                    <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="content">Content</label>
                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    required
                >{{ old('content', $article->content) }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('knowledge.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Article
            </button>
        </div>
    </form>
</div>
@endsection
