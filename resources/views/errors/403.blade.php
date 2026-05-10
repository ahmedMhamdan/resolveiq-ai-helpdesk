@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
    <section class="error-403-page">
        <div class="error-page-card">
            <div class="error-icon">🔒</div>

            <h1>Access Denied</h1>

            <p>You do not have permission to access this page.</p>

            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Back to Dashboard
            </a>
        </div>
    </section>
@endsection
