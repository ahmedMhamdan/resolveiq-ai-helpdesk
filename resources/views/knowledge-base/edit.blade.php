@extends('layouts.app')

@section('title', __('knowledge.title_edit'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('knowledge.title_edit') }}</h1>
        <p class="page-subtitle">{{ __('knowledge.edit_subtitle') }}</p>
    </div>

    <a href="{{ route('knowledge.index') }}" class="btn btn-secondary">
        {{ __('knowledge.back') }}
    </a>
</div>

<div class="table-card kb-form-note-card">
    <div class="table-head">
        <div>
            <h2>{{ __('knowledge.edit_note_heading') }}</h2>
            <p class="page-subtitle">{{ __('knowledge.edit_note_subtitle') }}</p>
        </div>
    </div>

    <div class="kb-form-note-body">
        <p class="text-muted">
            {{ __('knowledge.edit_note_text') }}
        </p>
    </div>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $article->title }}</h2>
            <p class="page-subtitle">{{ __('knowledge.edit_instructions') }}</p>
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
                <label for="title">{{ __('knowledge.article_title_label') }}</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $article->title) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">{{ __('knowledge.status_label') }}</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', $article->status) === 'published')>{{ __('knowledge.published_option') }}</option>
                    <option value="draft" @selected(old('status', $article->status) === 'draft')>{{ __('knowledge.draft_option') }}</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="content">{{ __('knowledge.content_label') }}</label>
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
                {{ __('knowledge.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('knowledge.update_article') }}
            </button>
        </div>
    </form>
</div>
@endsection