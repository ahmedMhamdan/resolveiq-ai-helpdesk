@extends('layouts.app')

@section('title', __('departments.title_create'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('departments.title_create') }}</h1>
        <p class="page-subtitle">{{ __('departments.create_subtitle') }}</p>
    </div>

    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        {{ __('departments.back') }}
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ __('departments.create_heading') }}</h2>
            <p class="page-subtitle">{{ __('departments.create_details') }}</p>
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

    <form action="{{ route('departments.store') }}" method="POST" class="ticket-form">
        @csrf

        <div class="form-grid">
            <div class="form-group full">
                <label for="name">{{ __('departments.department_name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="{{ __('departments.department_name_placeholder') }}"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="description">{{ __('departments.description_label') }}</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="{{ __('departments.description_placeholder') }}"
                >{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-danger-soft">
                {{ __('departments.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('departments.create_department') }}
            </button>
        </div>
    </form>
</div>
@endsection