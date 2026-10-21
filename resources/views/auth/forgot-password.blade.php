@extends('layouts.app')

@section('title', 'Forgot password · MyBlog')

@section('content')
<div class="auth-card">
    <div class="auth-side">
        <x-logo />
        <h2 class="mt-4 fw-bold">Forgot your password?</h2>
        <p class="opacity-75">No worries. Enter your email and we'll send you a link to set a new one.</p>
        <ul class="list-unstyled small opacity-75 mb-0">
            <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>The link works for 60 minutes</li>
            <li><i class="bi bi-check-circle-fill me-2"></i>Check your spam folder too</li>
        </ul>
    </div>

    <div class="auth-form">
        <h3 class="fw-bold mb-1">Reset password</h3>
        <p class="muted mb-4">We'll email you a reset link</p>

        @if(session('status'))
            <div class="alert alert-success py-2 small">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email address" required autofocus>
            </div>
            <button type="submit" class="btn btn-main">Send reset link</button>
        </form>

        <p class="text-center small muted mt-4 mb-0">
            <a href="{{ route('login') }}" class="fw-semibold text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to login</a>
        </p>
    </div>
</div>
@endsection