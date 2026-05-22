@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Edit Profile</h1>
        <p class="page-subtitle">Update your account information and profile picture.</p>
    </div>

    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="table-card ticket-create-card profile-edit-card">
    <div class="table-head">
        <div>
            <h2>Account Information</h2>
            <p class="page-subtitle">Change your profile name, email address, and avatar.</p>
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
        $profileAvatarUrl = $user->avatar_path ? $user->avatarUrl() : '';
    @endphp

    <form action="{{ route('profile.update') }}" method="POST" class="ticket-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="avatar">Profile picture</label>

                <div class="agent-avatar-uploader profile-avatar-uploader">
                    <div class="edit-avatar-preview" id="avatarPreview">
                        @if ($profileAvatarUrl)
                            <img
                                src="{{ $profileAvatarUrl }}"
                                alt="{{ $user->name }} avatar"
                                id="avatarPreviewImage"
                            >
                        @else
                            <div class="avatar-fallback" id="avatarFallback">
                                ?
                            </div>
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
            let existingImage = document.getElementById('avatarPreviewImage');
            const fallback = document.getElementById('avatarFallback');

            if (fallback) {
                fallback.remove();
            }

            if (!existingImage) {
                existingImage = document.createElement('img');
                existingImage.id = 'avatarPreviewImage';
                existingImage.alt = 'Avatar Preview';
                previewBox.innerHTML = '';
                previewBox.appendChild(existingImage);
            }

            existingImage.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endsection
