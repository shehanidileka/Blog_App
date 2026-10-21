@extends('layouts.app')

@section('title', 'Login · MyBlog')

@section('content')

<div class="auth-card">
    <div class="auth-side">
        <span class="orb o1"></span><span class="orb o2"></span>
        <x-logo />
        <h2 class="mt-4 fw-bold">Welcome back!</h2>
        <p class="lead-sm">Sign in to write posts, save your favourites and manage your profile.</p>

        <ul class="perks">
            <li><i class="bi bi-pencil-square"></i>Write and publish your own posts</li>
            <li><i class="bi bi-moon-stars"></i>Dark &amp; light mode everywhere</li>
            <li><i class="bi bi-gift"></i>Free forever</li>
        </ul>

        <div class="mock-wrap">
            <span class="pill-float p1"><i class="bi bi-pencil-fill"></i>Write</span>
            <span class="pill-float p2"><i class="bi bi-chat-dots-fill"></i>Comment</span>
            <span class="pill-float p3"><i class="bi bi-heart-fill"></i>React</span>
            <div class="mock">
                <div class="mock-img"></div>
                <div class="mock-line" style="width:85%"></div>
                <div class="mock-line" style="width:55%"></div>
                <div class="mock-foot"><span class="mock-av"></span><span class="mock-line" style="width:60px;margin:0"></span>
                    <span class="sp"><i class="bi bi-heart"></i><i class="bi bi-chat-dots"></i></span></div>
            </div>
        </div>
    </div>

    <div class="auth-form">
        <div class="auth-badge"><i class="bi bi-person-lock"></i></div>
        <div>
            <h3 class="fw-bold mb-1">Login</h3>
            <p class="muted mb-4">Sign in to your account</p>
        </div>

        @include('auth.partials.google', ['label' => 'Continue with Google'])

        <div class="divider"><span>or sign in with email</span></div>

        <div>
            @if(session('status'))
                <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('login') }}" data-auth-form novalidate>
            @csrf
            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <i class="bi bi-envelope f-icon"></i>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder=" " required autofocus autocomplete="email">
                <label for="email">Email address</label>
            </div>

            <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                <i class="bi bi-lock f-icon"></i>
                <input type="password" name="password" id="pw" placeholder=" " required autocomplete="current-password" data-caps="capsHint">
                <label for="pw">Password</label>
                <button type="button" class="f-toggle" data-toggle-pw="pw" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="f-hint" id="capsHint"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-main" data-label="Login" data-loading="Signing in…">
                <span class="spin"></span><span class="btn-text">Login</span><i class="bi bi-arrow-right arr"></i>
            </button>
        </form>

        <p class="text-center small muted mt-4 mb-0">
            Don't have an account? <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Register</a>
        </p>
    </div>
</div>

@include('auth.partials.script')
@endsection