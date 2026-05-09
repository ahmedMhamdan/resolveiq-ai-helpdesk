@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Profile</h1>
        <p class="page-subtitle">Update your account information.</p>
    </div>

    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>Account Information</h2>
            <p class="page-subtitle">Change your profile name and email address.</p>
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

    <form action="{{ route('profile.update') }}" method="POST" class="ticket-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Profile
            </button>
        </div>
    </form>
</div>
@endsection
