@extends('layouts.app')

@section('title', __('agents.title_edit'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('agents.title_edit') }}</h1>
        <p class="page-subtitle">{{ __('agents.edit_subtitle') }}</p>
    </div>

    <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-page-back">
        {{ __('agents.back_to_agents') }}
    </a>
</div>

<div class="table-card ticket-create-card agent-edit-card">
    <div class="table-head">
        <div>
            <h2>{{ $agent->name }}</h2>
            <p class="page-subtitle">{{ __('agents.edit_heading') }}</p>
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
                <label for="avatar">{{ __('agents.agent_picture') }}</label>

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
                            {{ __('agents.choose_image') }}
                        </label>

                        <span class="avatar-file-name" id="avatarFileName">{{ __('agents.no_image_selected') }}</span>

                        <small class="form-hint">{{ __('agents.upload_hint') }}</small>
                    </div>
                </div>

                @error('avatar')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">{{ __('agents.name_label') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $agent->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">{{ __('agents.email_label') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $agent->email) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">{{ __('agents.new_password') }}</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ __('agents.password_keep') }}"
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">{{ __('agents.confirm_password') }}</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="{{ __('agents.password_repeat') }}"
                >
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('agents.index') }}" class="btn btn-danger-soft">
                {{ __('agents.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('agents.update_agent') }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const fileName = document.getElementById('avatarFileName');
    const previewBox = document.getElementById('avatarPreview');
    const noImageSelected = @json(__('agents.no_image_selected'));

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
            let img = document.getElementById('avatarPreviewImage');
            const fallback = document.getElementById('avatarFallback');

            if (fallback) {
                fallback.remove();
            }

            if (!img) {
                img = document.createElement('img');
                img.id = 'avatarPreviewImage';
                img.alt = @json(__('common.avatar_preview'));
                previewBox.appendChild(img);
            }

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endsection