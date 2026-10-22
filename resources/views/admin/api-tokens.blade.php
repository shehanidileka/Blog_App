@extends('layouts.panel')

@section('title', 'API Tokens · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">API Tokens</h3>
    <p class="muted mb-0">Create a token for an app or script that uses the blog API (<code>Authorization: Bearer ...</code>).</p>
</div>

@if(session('new_token'))
    <div class="card-x p-3 p-md-4 mb-4" style="border:1px solid var(--bs-success, #2e9b57)">
        <h6 class="mb-2"><i class="bi bi-key me-1"></i>Your new token</h6>
        <div class="input-group">
            <input type="text" id="newToken" class="form-control" readonly value="{{ session('new_token') }}">
            <button type="button" class="btn btn-dash" onclick="navigator.clipboard.writeText(document.getElementById('newToken').value); this.textContent='Copied!'">Copy</button>
        </div>
        <p class="muted small mt-2 mb-0">Save it somewhere safe now. For security it cannot be shown again.</p>
    </div>
@endif

<div class="card-x p-4 mb-4">
    <h6 class="mb-3">New token</h6>
    <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="row g-3">
        @csrf
        <div class="col-md-5">
            <label class="form-label" for="t-user">User</label>
            <select id="t-user" name="user_id" class="form-select" required>
                <option value="">Select a user</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
            @error('user_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="col-md-5">
            <label class="form-label" for="t-name">Token name</label>
            <input type="text" id="t-name" name="name" class="form-control" required maxlength="100"
                   placeholder="e.g. Mobile app" value="{{ old('name') }}">
            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-dash w-100"><i class="bi bi-plus-lg me-1"></i>Create</button>
        </div>
    </form>
</div>

<div class="card-x p-3 p-md-4 mb-4">
    <h6 class="mb-3">Existing tokens</h6>
    <div class="table-responsive">
        <table class="table table-x align-middle mb-0">
            <thead><tr><th>Name</th><th>User</th><th>Created</th><th>Last used</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($tokens as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->name }}</td>
                        <td>{{ $t->tokenable?->name ?? '—' }}<br><small class="muted">{{ $t->tokenable?->email }}</small></td>
                        <td class="muted">{{ $t->created_at?->format('d M Y, H:i') }}</td>
                        <td class="muted">{{ $t->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.api-tokens.destroy', $t->id) }}" class="d-inline"
                                  onsubmit="return confirm('Revoke this token? Apps using it will stop working.')">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Revoke"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted text-center py-4">No tokens yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-x p-3 p-md-4">
    <h6 class="mb-3">Recent API requests <span class="muted small">(last 30)</span></h6>
    <div class="table-responsive">
        <table class="table table-x align-middle mb-0">
            <thead><tr><th>When</th><th>Method</th><th>Path</th><th>Status</th><th>IP</th><th>Time</th></tr></thead>
            <tbody>
                @forelse($logs as $l)
                    <tr>
                        <td class="muted text-nowrap">{{ $l->created_at?->format('d M, H:i:s') }}</td>
                        <td><code>{{ $l->method }}</code></td>
                        <td><code>/{{ $l->path }}</code></td>
                        <td>
                            <span class="{{ $l->status_code >= 400 ? 'text-danger' : 'text-success' }} fw-semibold">{{ $l->status_code }}</span>
                        </td>
                        <td class="muted">{{ $l->ip_address }}</td>
                        <td class="muted">{{ $l->duration_ms }} ms</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted text-center py-4">No API requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection