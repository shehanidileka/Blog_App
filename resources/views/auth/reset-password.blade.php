@extends('layouts.app')

@section('title', 'Enter code · MyBlog')

@section('content')
<div class="auth-card">
    <div class="auth-side">
        <x-logo />
        <h2 class="mt-4 fw-bold">Check your email</h2>
        <p class="opacity-75">We sent a 6-digit code to your email. Enter it with your new password.</p>
        <ul class="list-unstyled small opacity-75 mb-0">
            <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>The code expires in 10 minutes</li>
            <li><i class="bi bi-check-circle-fill me-2"></i>Check your spam folder too</li>
        </ul>
    </div>

    <div class="auth-form">
        <h3 class="fw-bold mb-1">Reset password</h3>
        <p class="muted mb-4">Enter the code and a new password</p>

        @if(session('status'))
            <div class="alert alert-success py-2 small">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" placeholder="Email address" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                <input type="text" name="code" inputmode="numeric" maxlength="6" pattern="\d{6}" class="form-control" placeholder="6-digit code" required autofocus autocomplete="one-time-code">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="pw" class="form-control" placeholder="New password" required>
                <button type="button" class="input-group-text" id="togglePw"><i class="bi bi-eye"></i></button>
            </div>
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password_confirmation" id="pw2" class="form-control" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn btn-main">Reset password</button>
        </form>

        <p class="text-center small muted mt-4 mb-0">
            Didn't get a code? <a href="{{ route('password.request') }}" class="fw-semibold text-decoration-none">Send again</a>
        </p>
    </div>
</div>

<script>
    document.getElementById('togglePw').addEventListener('click', () => {
        const t = document.getElementById('pw').type === 'password' ? 'text' : 'password';
        document.getElementById('pw').type = t;
        document.getElementById('pw2').type = t;
    });
</script>
@endsection