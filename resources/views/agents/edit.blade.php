@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Agent</h1>
        <p class="page-subtitle">Update agent account information and profile picture.</p>
    </div>

    <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-page-back">
        Back to Agents
    </a>
</div>

<div class="table-card ticket-create-card agent-edit-card">
    <div class="table-head">
        <div>
            <h2>{{ $agent->name }}</h2>
            <p class="page-subtitle">Change the agent name, email, password, or avatar.</p>
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

    @php
        $agentAvatarUrl = $agent->avatar_path ? $agent->avatarUrl() : '';
    @endphp

    <form action="{{ route('agents.update', $agent) }}" method="POST" class="ticket-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="avatar">Agent picture</label>

                <div class="agent-avatar-uploader">
                    <div class="edit-avatar-preview" id="avatarPreview">
                        @if ($agentAvatarUrl)
                            <img
                                src="{{ $agentAvatarUrl }}"
                                alt="{{ $agent->name }} avatar"
                                id="avatarPreviewImage"
                            >
                        @else
                            <div class="avatar-fallback" id="avatarFallback">?</div>
                        @endif
                    </div>

                    <div class="avatar-upload-content">
                        <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            class="avatar-file-input"
                            accept="image/png,image/jpeg,image/jpg,image/webp"
                        >

                        <label for="avatar" class="avatar-upload-btn">
                            Choose Image
                        </label>

                        <span class="avatar-file-name" id="avatarFileName">No image selected</span>

                        <small class="form-hint">Upload JPG, PNG, or WebP. Max size 2MB.</small>
                    </div>
                </div>

                @error('avatar')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">Name</label>
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

            <div class="form-group">
                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Leave empty to keep current password"
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Repeat new password"
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const fileName = document.getElementById('avatarFileName');
    const previewBox = document.getElementById('avatarPreview');

    if (!avatarInput || !fileName || !previewBox) return;

    avatarInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) {
            fileName.textContent = 'No image selected';
            return;
        }

        fileName.textContent = file.name;

        const reader = new FileReader();

        reader.onload = function (e) {
            let img = document.getElementById('avatarPreviewImage');
            const fallback = document.getElementById('avatarFallback');

            if (fallback) {
                fallback.remove();
            }

            if (!img) {
                img = document.createElement('img');
                img.id = 'avatarPreviewImage';
                img.alt = 'Avatar preview';
                previewBox.appendChild(img);
            }

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endsection
