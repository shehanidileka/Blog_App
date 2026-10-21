@extends('layouts.panel')

@section('title', 'Mail Settings · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Mail Settings</h3>
    <p class="muted mb-0">Configure the SMTP server used to send emails from MyBlog.</p>
</div>

{{-- SMTP form --}}
<div class="card-x p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h6 class="mb-0"><i class="bi bi-envelope-at me-1"></i>SMTP Configuration</h6>
        <div class="d-flex flex-wrap gap-2">
            <span class="muted small align-self-center">Quick fill:</span>
            <button type="button" class="chip" data-preset="gmail">Gmail</button>
            <button type="button" class="chip" data-preset="outlook">Outlook</button>
            <button type="button" class="chip" data-preset="mailtrap">Mailtrap</button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.mail.update') }}">
        @csrf
        @method('PUT')

        <div class="form-check form-switch mb-4">
            <input type="hidden" name="enabled" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1"
                   {{ old('enabled', $settings['enabled'] ? 1 : 0) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="enabled">Enable custom SMTP</label>
            <div class="muted small">When turned off, the mail settings from the <code>.env</code> file are used.</div>
        </div>

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="host">SMTP Host</label>
                <input type="text" id="host" name="host" class="form-control" placeholder="smtp.gmail.com"
                       value="{{ old('host', $settings['host']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="port">Port</label>
                <input type="number" id="port" name="port" class="form-control" placeholder="587" min="1" max="65535"
                       value="{{ old('port', $settings['port']) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="encryption">Encryption</label>
                <select id="encryption" name="encryption" class="form-select">
                    @foreach(['tls' => 'TLS (STARTTLS, port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None'] as $val => $label)
                        <option value="{{ $val }}" {{ old('encryption', $settings['encryption']) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" autocomplete="off"
                       placeholder="you@example.com" value="{{ old('username', $settings['username']) }}">
            </div>

            <div class="col-12">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" autocomplete="new-password"
                           placeholder="{{ $settings['has_password'] ? '•••••••• (saved - leave blank to keep it)' : 'SMTP password or app password' }}">
                    <button type="button" class="btn btn-nav-outline" id="togglePw" title="Show / hide">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @if($settings['has_password'])
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="clear_password" name="clear_password" value="1">
                        <label class="form-check-label small muted" for="clear_password">Remove the saved password</label>
                    </div>
                @endif
                <div class="muted small mt-1">The password is stored encrypted and is never shown again.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="from_address">From Address</label>
                <input type="email" id="from_address" name="from_address" class="form-control" required
                       value="{{ old('from_address', $settings['from_address']) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="from_name">From Name</label>
                <input type="text" id="from_name" name="from_name" class="form-control" required
                       value="{{ old('from_name', $settings['from_name']) }}">
            </div>
        </div>

        <div class="alert alert-info rounded-3 py-2 small mt-4 mb-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Gmail:</strong> turn on 2-Step Verification and use an <em>App Password</em> (not your normal Gmail password).
        </div>

        <button type="submit" class="btn btn-dash"><i class="bi bi-check-lg me-1"></i>Save settings</button>
    </form>
</div>

{{-- Test email --}}
<div class="card-x p-4">
    <h6 class="mb-1"><i class="bi bi-send me-1"></i>Send a test email</h6>
    <p class="muted small mb-3">Save your settings first, then send a test message to check that everything works.</p>

    <form method="POST" action="{{ route('admin.settings.mail.test') }}" class="d-flex flex-wrap gap-2">
        @csrf
        <input type="email" name="test_email" class="form-control" style="max-width:360px" required
               placeholder="recipient@example.com" value="{{ old('test_email', auth()->user()->email) }}">
        <button type="submit" class="btn btn-dash text-nowrap"><i class="bi bi-send me-1"></i>Send test</button>
    </form>
</div>

<script>
    (function () {
        var presets = {
            gmail:    { host: 'smtp.gmail.com',            port: 587,  enc: 'tls' },
            outlook:  { host: 'smtp.office365.com',        port: 587,  enc: 'tls' },
            mailtrap: { host: 'sandbox.smtp.mailtrap.io',  port: 2525, enc: 'tls' }
        };

        document.querySelectorAll('[data-preset]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var p = presets[btn.dataset.preset];
                document.getElementById('host').value = p.host;
                document.getElementById('port').value = p.port;
                document.getElementById('encryption').value = p.enc;
                document.getElementById('enabled').checked = true;
            });
        });

        document.getElementById('togglePw').addEventListener('click', function () {
            var input = document.getElementById('password');
            var icon = this.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    })();
</script>
@endsection