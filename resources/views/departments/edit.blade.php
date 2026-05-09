@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Department</h1>
        <p class="page-subtitle">Update department information.</p>
    </div>

    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $department->name }}</h2>
            <p class="page-subtitle">Edit this department below.</p>
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
                <label for="name">Department Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $department->name) }}"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                >{{ old('description', $department->description) }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Department
            </button>
        </div>
    </form>
</div>
@endsection
