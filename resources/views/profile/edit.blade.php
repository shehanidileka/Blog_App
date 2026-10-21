@extends('layouts.panel')

@section('title', 'My Profile · MyBlog')

@section('panel')

@php
    $tab = $errors->getBag('password')->any() ? 'security' : 'profile';
    $isGoogle = (bool) $user->google_id;
@endphp

{{-- Banner --}}
<div class="card-x pf-card mb-4">
    <div class="pf-cover"></div>

    <div class="pf-head">
        <div class="pf-avatar-wrap">
            <div class="pf-avatar">
                <img id="avatarImg" src="{{ $user->avatar }}" alt="" referrerpolicy="no-referrer" @if(! $user->avatar) style="display:none" @endif>
                <span id="avatarInitial" @if($user->avatar) style="display:none" @endif>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
            <label for="avatarInput" class="pf-cam" title="Change photo"><i class="bi bi-camera-fill"></i></label>
            {{-- belongs to the profile form below --}}
            <input type="file" name="avatar" id="avatarInput" form="profileForm" accept="image/png,image/jpeg,image/webp" hidden>
        </div>
        <div class="pf-id">
            <h4 class="text-truncate">{{ $user->name }}</h4>
            <div class="muted small text-truncate">{{ $user->email }}</div>
        </div>
    </div>

    <div class="pf-photo-note" id="photoNote"><i class="bi bi-info-circle me-1"></i>New photo selected. Click <b>Save changes</b> to apply it.</div>

    <div class="pf-meta">
        <span class="role-badge {{ $user->role === 'admin' ? 'admin' : 'user' }}">{{ ucfirst($user->role ?: 'user') }}</span>
        <span class="pf-pill"><i class="bi bi-calendar3"></i>Joined {{ $user->created_at->format('M Y') }}</span>
        @if($isGoogle)
            <span class="pf-pill"><i class="bi bi-google"></i>Google connected</span>
        @endif
        @if($user->website)
            <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer" class="pf-pill text-decoration-none"><i class="bi bi-link-45deg"></i>{{ parse_url($user->website, PHP_URL_HOST) ?: $user->website }}</a>
        @endif
        @if($user->avatar)
            <button type="submit" form="removeAvatarForm" class="btn btn-link btn-sm text-danger text-decoration-none ms-auto p-0"
                    onclick="return confirm('Remove your profile photo?')"><i class="bi bi-trash me-1"></i>Remove photo</button>
        @endif
    </div>

    <p class="pf-bio">{{ $user->bio ?: 'No bio yet. Tell the community a little about yourself.' }}</p>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card-x stat pf-stat">
            <div class="stat-icon"><i class="bi bi-journal-richtext"></i></div>
            <div><div class="stat-num">{{ number_format($stats['posts']) }}</div><small class="muted">My posts</small></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-x stat pf-stat">
            <div class="stat-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a)"><i class="bi bi-check2-circle"></i></div>
            <div><div class="stat-num">{{ number_format($stats['published']) }}</div><small class="muted">Published</small></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-x stat pf-stat">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#f97316)"><i class="bi bi-eye"></i></div>
            <div><div class="stat-num">{{ number_format($stats['views']) }}</div><small class="muted">Total views</small></div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="pf-tabs" role="tablist">
    <button type="button" class="pf-tab {{ $tab === 'profile' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-profile" role="tab"><i class="bi bi-person me-1"></i>Profile</button>
    <button type="button" class="pf-tab {{ $tab === 'security' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-security" role="tab"><i class="bi bi-shield-lock me-1"></i>Security</button>
</div>

<div class="tab-content">

    {{-- PROFILE --}}
    <div class="tab-pane fade {{ $tab === 'profile' ? 'show active' : '' }}" id="tab-profile" role="tabpanel">
        <div class="card-x p-3 p-md-4">
            <h5 class="pf-section-title">Personal information</h5>
            <p class="muted small mb-4">This is how you appear on your posts and comments.</p>

            <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror"
                               {{ $isGoogle ? 'readonly' : 'required' }}>
                        @if($isGoogle)
                            <div class="form-text muted">Managed by your Google account.</div>
                        @endif
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="website">Website <span class="muted">(optional)</span></label>
                        <input type="url" id="website" name="website" value="{{ old('website', $user->website) }}" class="form-control @error('website') is-invalid @enderror" placeholder="https://example.com">
                        @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="bio">Bio <span class="muted">(optional)</span></label>
                        <textarea id="bio" name="bio" rows="4" maxlength="300" class="form-control @error('bio') is-invalid @enderror" placeholder="Tell the community a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        <div class="pf-counter"><span id="bioCount">0</span> / 300</div>
                        @error('bio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-dash"><i class="bi bi-check2 me-1"></i>Save changes</button>
                    <a href="{{ route('profile.edit') }}" class="btn btn-nav-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- SECURITY --}}
    <div class="tab-pane fade {{ $tab === 'security' ? 'show active' : '' }}" id="tab-security" role="tabpanel">
        <div class="card-x p-3 p-md-4">
            <h5 class="pf-section-title">{{ $isGoogle ? 'Set a password' : 'Change password' }}</h5>
            <p class="muted small mb-4">
                @if($isGoogle)
                    You signed in with Google. Set a password if you also want to log in with your email.
                @else
                    Use at least 8 characters. A longer password with numbers and symbols is safer.
                @endif
            </p>

            @if($errors->getBag('password')->any())
                <div class="alert alert-danger rounded-3 py-2">
                    <ul class="mb-0 small">
                        @foreach($errors->getBag('password')->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password') }}" style="max-width:480px">
                @csrf
                @method('PUT')

                @unless($isGoogle)
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current password</label>
                        <div class="pf-pw">
                            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                            <button type="button" data-pf-toggle="current_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                @endunless

                <div class="mb-3">
                    <label class="form-label" for="new_password">New password</label>
                    <div class="pf-pw">
                        <input type="password" id="new_password" name="password" class="form-control" autocomplete="new-password" required>
                        <button type="button" data-pf-toggle="new_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="new_password_confirmation">Confirm new password</label>
                    <div class="pf-pw">
                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        <button type="button" data-pf-toggle="new_password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn btn-dash"><i class="bi bi-shield-check me-1"></i>{{ $isGoogle ? 'Set password' : 'Update password' }}</button>
            </form>
        </div>
    </div>
</div>

{{-- Remove avatar (kept outside the main form) --}}
<form id="removeAvatarForm" method="POST" action="{{ route('profile.avatar.remove') }}" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
(function () {
    // Live photo preview
    const input = document.getElementById('avatarInput');
    const img = document.getElementById('avatarImg');
    const initial = document.getElementById('avatarInitial');
    const note = document.getElementById('photoNote');

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Please choose an image smaller than 2 MB.');
            input.value = '';
            return;
        }
        img.src = URL.createObjectURL(file);
        img.style.display = 'block';
        initial.style.display = 'none';
        note.classList.add('show');
    });

    // Bio counter
    const bio = document.getElementById('bio'), count = document.getElementById('bioCount');
    const upd = () => count.textContent = bio.value.length;
    bio.addEventListener('input', upd); upd();

    // Show / hide password
    document.querySelectorAll('[data-pf-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const f = document.getElementById(btn.dataset.pfToggle);
            const show = f.type === 'password';
            f.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
})();
</script>
@endsection