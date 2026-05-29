@extends('layouts.app')

@section('title', __('profile.title_edit'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('profile.title_edit') }}</h1>
        <p class="page-subtitle">{{ __('profile.edit_subtitle') }}</p>
    </div>

    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        {{ __('profile.back') }}
    </a>
</div>

<div class="table-card ticket-create-card profile-edit-card">
    <div class="table-head">
        <div>
            <h2>{{ __('profile.account_info') }}</h2>
            <p class="page-subtitle">{{ __('profile.account_info_subtitle') }}</p>
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
                <label for="avatar">{{ __('profile.profile_picture') }}</label>

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
                            {{ __('profile.choose_image') }}
                        </label>

                        <span class="avatar-file-name" id="avatarFileName">{{ __('profile.no_image_selected') }}</span>

                        <small class="form-hint">{{ __('profile.upload_hint') }}</small>
                    </div>
                </div>

                @error('avatar')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">{{ __('profile.name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">{{ __('profile.email_label') }}</label>
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
                {{ __('profile.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('profile.update_profile') }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const fileName = document.getElementById('avatarFileName');
    const previewBox = document.getElementById('avatarPreview');
    const noImageSelected = @json(__('profile.no_image_selected'));

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