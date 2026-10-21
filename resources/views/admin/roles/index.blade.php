@extends('layouts.panel')

@section('title', 'Roles & Permissions · Admin')

@section('panel')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Roles & Permissions</h3>
        <p class="muted mb-0">Control what each role is allowed to do.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-nav-outline"><i class="bi bi-key me-1"></i>Manage permissions</a>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-dash"><i class="bi bi-plus-lg me-1"></i>New role</a>
    </div>
</div>

<div class="card-x p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-x table-hover align-middle mb-0">
            <thead>
                <tr><th>Role</th><th>Users</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ $role->name }}
                                @if($role->is_system)
                                    <span class="role-badge user ms-1">System</span>
                                @endif
                            </div>
                            <small class="muted">{{ $role->description }}</small>
                        </td>
                        <td class="muted">{{ $role->users_count }}</td>
                        <td class="muted">{{ $role->slug === 'admin' ? 'All' : $role->permissions_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn-icon"
                               title="{{ $role->slug === 'admin' ? 'View' : 'Edit' }}">
                                <i class="bi {{ $role->slug === 'admin' ? 'bi-eye' : 'bi-pencil' }}"></i>
                            </a>
                            @if(! $role->is_system)
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this role?')">
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
</div>
@endsection