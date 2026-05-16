@extends('layouts.app')

@section('title', 'New Article')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">New Article</h1>
        <p class="page-subtitle">Create a reusable support article for agents and AI-assisted replies.</p>
    </div>

    <a href="{{ route('knowledge.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card" style="margin-bottom: 20px;">
    <div class="table-head">
        <div>
            <h2>Article writing guide</h2>
            <p class="page-subtitle">Keep the article clear, practical, and easy to reuse inside tickets.</p>
        </div>
    </div>

    <p class="text-muted" style="line-height: 1.8; margin-bottom: 0;">
        Use a direct title, explain the problem, then write the solution steps. Published articles can be used later
        as context for the AI Assistant, while draft articles stay hidden from AI usage until they are ready.
    </p>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>Create Article</h2>
            <p class="page-subtitle">Write a clear article for common support cases.</p>
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
                    placeholder="Example:\nProblem: The user cannot access the account.\nSolution:\n1. Ask the user to confirm the email.\n2. Send a password reset link.\n3. Check if 2FA is enabled."
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
