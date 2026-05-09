@extends('layouts.app')

@section('title', 'New Department')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">New Department</h1>
        <p class="page-subtitle">Create a department for ticket routing.</p>
    </div>

    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>Create Department</h2>
            <p class="page-subtitle">Fill in department information.</p>
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
                <label for="name">Department Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Example: Technical Support"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Short description about this department..."
                >{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Department
            </button>
        </div>
    </form>
</div>
@endsection
