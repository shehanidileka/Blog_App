@extends('layouts.panel')

@section('title', 'Users · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Users</h3>
    <p class="muted mb-0">Manage roles and accounts.</p>
</div>

<div class="card-x p-3 p-md-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email...">
        <button class="btn btn-dash"><i class="bi bi-search"></i></button>
        @if(request('q'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-nav-outline">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-x table-hover align-middle mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Posts</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($users as $u)
                    @php
                        $canEdit = $u->id !== auth()->id() && (auth()->user()->isAdmin() || ! $u->isAdmin());
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($u->avatar)
                                    <img src="{{ $u->avatar }}" class="avatar" alt="" referrerpolicy="no-referrer">
                                @else
                                    <span class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $u->name }}@if($u->id === auth()->id()) <small class="muted">(you)</small>@endif</div>
                                    <small class="muted">{{ $u->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($canEdit)
                                <form method="POST" action="{{ route('admin.users.role', $u) }}">
                                    @csrf @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="min-width:140px" onchange="this.form.submit()">
                                        @foreach($roles as $r)
                                            @if($r->slug !== 'admin' || auth()->user()->isAdmin())
                                                <option value="{{ $r->slug }}" {{ $u->role === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="role-badge {{ $u->isAdmin() ? 'admin' : 'user' }}">{{ $u->roleModel->name ?? ucfirst($u->role) }}</span>
                            @endif
                        </td>
                        <td class="muted">{{ $u->posts_count }}</td>
                        <td class="muted">{{ $u->created_at->format('M d, Y') }}</td>
                        <td class="text-end text-nowrap">
                            @if($canEdit)
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Delete this user and all their posts?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $users->links() }}</div>
    @endif
</div>
@endsection