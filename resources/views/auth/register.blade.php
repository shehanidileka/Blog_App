@extends('layouts.app')

@section('title', 'Register · MyBlog')

@section('content')

<div class="auth-card">
    <div class="auth-side">
        <span class="orb o1"></span><span class="orb o2"></span>
        <x-logo />
        <h2 class="mt-4 fw-bold">Join MyBlog</h2>
        <p class="lead-sm">Create a free account and start sharing your stories with the world.</p>

        <ul class="perks">
            <li><i class="bi bi-stopwatch"></i>Takes less than a minute</li>
            <li><i class="bi bi-google"></i>Sign up with Google in one click</li>
            <li><i class="bi bi-person-badge"></i>Your own profile page</li>
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
        <div class="auth-badge"><i class="bi bi-person-plus"></i></div>
        <div>
            <h3 class="fw-bold mb-1">Create account</h3>
            <p class="muted mb-4">It's free and easy</p>
        </div>

        @include('auth.partials.google', ['label' => 'Sign up with Google'])

        <div class="divider"><span>or register with email</span></div>

        <div>
            @if($errors->any())
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('register') }}" data-auth-form novalidate>
            @csrf
            <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                <i class="bi bi-person f-icon"></i>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder=" " required autofocus autocomplete="name">
                <label for="name">Full name</label>
            </div>

            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <i class="bi bi-envelope f-icon"></i>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email">
                <label for="email">Email address</label>
            </div>

            <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                <i class="bi bi-lock f-icon"></i>
                <input type="password" name="password" id="pw" placeholder=" " required autocomplete="new-password" data-caps="capsHint">
                <label for="pw">Password (min 8 characters)</label>
                <button type="button" class="f-toggle" data-toggle-pw="pw" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="f-hint" id="capsHint"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>

            <div class="strength" id="strength" data-level="0">
                <div class="bars"><span></span><span></span><span></span><span></span></div>
                <div class="meta">
                    <div class="rules">
                        <em data-rule="len">8+ chars</em><em data-rule="case">Aa</em><em data-rule="num">123</em><em data-rule="sym">#@!</em>
                    </div>
                    <b id="strengthLabel"></b>
                </div>
            </div>

            <div class="field mb-3">
                <i class="bi bi-shield-lock f-icon"></i>
                <input type="password" name="password_confirmation" id="pw2" placeholder=" " required autocomplete="new-password">
                <label for="pw2">Confirm password</label>
                <button type="button" class="f-toggle" data-toggle-pw="pw2" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="match-msg" id="matchMsg"></div>

            <button type="submit" class="btn btn-main mt-2" data-label="Create account" data-loading="Creating account…">
                <span class="spin"></span><span class="btn-text">Create account</span><i class="bi bi-arrow-right arr"></i>
            </button>
        </form>

        <p class="text-center small muted mt-4 mb-0">
            Already have an account? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Login</a>
        </p>
    </div>
</div>

@include('auth.partials.script')
<script>
(function () {
    const pw = document.getElementById('pw'), pw2 = document.getElementById('pw2');
    const box = document.getElementById('strength'), label = document.getElementById('strengthLabel');
    const msg = document.getElementById('matchMsg');
    const names = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    function strength() {
        const v = pw.value;
        const r = { len: v.length >= 8, case: /[a-z]/.test(v) && /[A-Z]/.test(v), num: /\d/.test(v), sym: /[^A-Za-z0-9]/.test(v) };
        box.querySelectorAll('[data-rule]').forEach(el => el.classList.toggle('ok', r[el.dataset.rule]));
        let level = Object.values(r).filter(Boolean).length;
        if (!r.len) level = Math.min(level, 1);
        box.classList.toggle('show', v.length > 0);
        box.dataset.level = v.length ? Math.max(level, 1) : 0;
        label.textContent = v.length ? names[Math.max(level, 1)] : '';
        match();
    }
    function match() {
        if (!pw2.value) { msg.className = 'match-msg'; return; }
        const ok = pw.value === pw2.value;
        msg.className = 'match-msg show ' + (ok ? 'ok' : 'bad');
        msg.innerHTML = ok ? '<i class="bi bi-check-circle me-1"></i>Passwords match'
                           : '<i class="bi bi-x-circle me-1"></i>Passwords do not match';
    }
    pw.addEventListener('input', strength);
    pw2.addEventListener('input', match);
})();
</script>
@endsection