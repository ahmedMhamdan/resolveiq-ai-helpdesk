@extends('layouts.app')

@section('title', 'New Article')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">New Article</h1>
        <p class="page-subtitle">Create a support knowledge base article.</p>
    </div>

    <a href="{{ route('knowledge.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>Create Article</h2>
            <p class="page-subtitle">Write a clear article for agents.</p>
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

    <form action="{{ route('knowledge.store') }}" method="POST" class="ticket-form">
        @csrf

        <div class="form-grid">
            <div class="form-group full">
                <label for="title">Article Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Example: How to reset 2FA"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
                    <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="content">Content</label>
                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    placeholder="Write the article content..."
                    required
                >{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('knowledge.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Article
            </button>
        </div>
    </form>
</div>
@endsection
