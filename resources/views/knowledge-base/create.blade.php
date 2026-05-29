@extends('layouts.app')

@section('title', __('knowledge.title_create'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('knowledge.title_create') }}</h1>
        <p class="page-subtitle">{{ __('knowledge.create_subtitle') }}</p>
    </div>

    <a href="{{ route('knowledge.index') }}" class="btn btn-secondary">
        {{ __('knowledge.back') }}
    </a>
</div>

<div class="table-card" style="margin-bottom: 20px;">
    <div class="table-head">
        <div>
            <h2>{{ __('knowledge.guide_heading') }}</h2>
            <p class="page-subtitle">{{ __('knowledge.guide_subtitle') }}</p>
        </div>
    </div>

    <p class="text-muted" style="line-height: 1.8; margin-bottom: 0;">
        {{ __('knowledge.guide_text') }}
    </p>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ __('knowledge.create_heading') }}</h2>
            <p class="page-subtitle">{{ __('knowledge.create_details') }}</p>
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
                <label for="title">{{ __('knowledge.article_title_label') }}</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="{{ __('knowledge.title_placeholder') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">{{ __('knowledge.status_label') }}</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', 'published') === 'published')>{{ __('knowledge.published_option') }}</option>
                    <option value="draft" @selected(old('status') === 'draft')>{{ __('knowledge.draft_option') }}</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="content">{{ __('knowledge.content_label') }}</label>
                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    placeholder="{{ __('knowledge.content_placeholder') }}"
                    required
                >{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('knowledge.index') }}" class="btn btn-danger-soft">
                {{ __('knowledge.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('knowledge.create_article') }}
            </button>
        </div>
    </form>
</div>
@endsection