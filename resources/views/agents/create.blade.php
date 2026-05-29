@extends('layouts.app')

@section('title', __('agents.title_create'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('agents.title_create') }}</h1>
        <p class="page-subtitle">{{ __('agents.create_subtitle') }}</p>
    </div>

    <a href="{{ route('agents.index') }}" class="btn btn-secondary">
        {{ __('agents.back') }}
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ __('agents.create_heading') }}</h2>
            <p class="page-subtitle">{{ __('agents.create_details') }}</p>
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

    <form action="{{ route('agents.store') }}" method="POST" class="ticket-form">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label for="name">{{ __('agents.agent_name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="{{ __('agents.agent_name_placeholder') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">{{ __('agents.email_label') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('agents.email_placeholder') }}"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="password">{{ __('agents.password_label') }}</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ __('agents.password_placeholder') }}"
                    required
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('agents.index') }}" class="btn btn-danger-soft">
                {{ __('agents.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('agents.create_agent') }}
            </button>
        </div>
    </form>
</div>
@endsection