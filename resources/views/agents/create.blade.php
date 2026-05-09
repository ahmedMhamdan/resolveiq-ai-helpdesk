@extends('layouts.app')

@section('title', 'New Agent')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">New Agent</h1>
        <p class="page-subtitle">Create a new support agent account.</p>
    </div>

    <a href="{{ route('agents.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>Create Agent</h2>
            <p class="page-subtitle">Fill in agent account details.</p>
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
                <label for="name">Agent Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Example: Support Agent"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="agent@resolveiq.test"
                    required
                >
            </div>

            <div class="form-group full">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('agents.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Agent
            </button>
        </div>
    </form>
</div>
@endsection
