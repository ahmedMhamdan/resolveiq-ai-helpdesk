@extends('layouts.app')

@section('title', __('users.title_edit'))

@section('content')
@php
    $roleName = strtolower($user->role?->name ?? 'user');

    $avatarUrl = null;

    if ($user->avatar_path) {
        $avatarUrl = method_exists($user, 'avatarUrl')
            ? $user->avatarUrl()
            : (str_starts_with($user->avatar_path, 'images/')
                ? asset($user->avatar_path)
                : asset('storage/' . $user->avatar_path));
    }
@endphp

<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('users.title_edit') }}</h1>
        <p class="page-subtitle">{{ __('users.edit_subtitle') }}</p>
    </div>

    <a href="{{ url('/users/' . $user->id) }}" class="btn btn-secondary">
        {{ __('users.back') }}
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="table-head">
        <div>
            <h2>{{ $user->name }}</h2>
            <p class="page-subtitle">{{ __('users.password_keep') }}</p>
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

    <form action="{{ url('/users/' . $user->id) }}" method="POST" class="ticket-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="avatar">{{ __('users.user_avatar_label') }}</label>

                <div class="agent-avatar-uploader">
                    <div class="edit-avatar-preview" id="avatarPreview">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }} avatar" id="avatarPreviewImage">
                        @else
                            <div class="avatar-fallback" id="avatarFallback">
                                <span class="avatar-fallback">?</span>
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
                            {{ __('users.choose_image') }}
                        </label>

                        <span class="avatar-file-name" id="avatarFileName">{{ __('users.no_image_selected') }}</span>
                        <small class="form-hint">{{ __('users.upload_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="name">{{ __('users.user_name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">{{ __('users.email_label') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">{{ __('users.new_password') }}</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ __('users.password_placeholder') }}"
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">{{ __('users.confirm_password') }}</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="{{ __('users.password_repeat_placeholder') }}"
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ url('/users/' . $user->id) }}" class="btn btn-danger-soft">
                {{ __('users.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('users.update_user') }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const fileName = document.getElementById('avatarFileName');
    const previewBox = document.getElementById('avatarPreview');
    const noImageSelected = @json(__('users.no_image_selected'));

    if (!avatarInput || !fileName || !previewBox) return;

    avatarInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) {
            fileName.textContent = noImageSelected;
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
                existingImage.alt = @json(__('common.avatar_preview'));
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