@extends('layouts.app')

@section('title', __('departments.title_edit'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('departments.title_edit') }}</h1>
        <p class="page-subtitle">{{ __('departments.edit_subtitle') }}</p>
    </div>

    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        {{ __('departments.back') }}
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $department->name }}</h2>
            <p class="page-subtitle">{{ __('departments.edit_instructions') }}</p>
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

    <form action="{{ route('departments.update', $department) }}" method="POST" class="ticket-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="name">{{ __('departments.department_name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $department->name) }}"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="description">{{ __('departments.description_label') }}</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                >{{ old('description', $department->description) }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-danger-soft">
                {{ __('departments.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('departments.update_department') }}
            </button>
        </div>
    </form>
</div>
@endsection