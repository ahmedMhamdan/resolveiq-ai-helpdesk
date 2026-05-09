@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Agent</h1>
        <p class="page-subtitle">Update support agent information.</p>
    </div>

    <a href="{{ route('agents.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $agent->name }}</h2>
            <p class="page-subtitle">Leave password empty to keep the current one.</p>
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

    <form action="{{ route('agents.update', $agent) }}" method="POST" class="ticket-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Agent Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $agent->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $agent->email) }}"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Leave empty to keep current password"
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('agents.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Agent
            </button>
        </div>
    </form>
</div>
@endsection
