@extends('layouts.panel')

@section('title', ($role->exists ? 'Edit Role' : 'New Role') . ' · Admin')

@section('panel')
@php $locked = $role->exists && $role->slug === 'admin'; @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">{{ $role->exists ? ($locked ? 'View Role' : 'Edit Role') : 'New Role' }}</h3>
        <p class="muted mb-0">Choose what this role is allowed to do.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-nav-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

@if($locked)
    <div class="alert alert-info rounded-3 py-2 small">
        <i class="bi bi-lock me-1"></i>The Admin role always has full access and cannot be changed.
    </div>
@endif

<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
    @csrf
    @if($role->exists) @method('PUT') @endif

    <div class="card-x p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label" for="name">Role name</label>
                <input type="text" id="name" name="name" class="form-control" required maxlength="50"
                       placeholder="e.g. Editor" value="{{ old('name', $role->name) }}" {{ $locked ? 'readonly' : '' }}>
            </div>
            <div class="col-md-7">
                <label class="form-label" for="description">Description</label>
                <input type="text" id="description" name="description" class="form-control" maxlength="255"
                       placeholder="What is this role for?" value="{{ old('description', $role->description) }}" {{ $locked ? 'readonly' : '' }}>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach($groups as $group => $perms)
            <div class="col-md-6">
                <div class="card-x p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ $group }}</h6>
                        @unless($locked)
                            <a href="#" class="small" data-toggle-group>Select all / none</a>
                        @endunless
                    </div>

                    @foreach($perms as $p)
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="permissions[]"
                                   value="{{ $p->id }}" id="perm{{ $p->id }}"
                                   {{ $locked || in_array($p->id, old('permissions', $selected)) ? 'checked' : '' }}
                                   {{ $locked ? 'disabled' : '' }}>
                            <label class="form-check-label" for="perm{{ $p->id }}">
                                <span class="fw-semibold">{{ $p->name }}</span>
                                <span class="d-block muted small">{{ $p->description }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @unless($locked)
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-dash px-4">{{ $role->exists ? 'Update Role' : 'Create Role' }}</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-nav-outline">Cancel</a>
        </div>
    @endunless
</form>

<script>
    document.querySelectorAll('[data-toggle-group]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var boxes = link.closest('.card-x').querySelectorAll('input[type=checkbox]');
            var allOn = Array.from(boxes).every(function (b) { return b.checked; });
            boxes.forEach(function (b) { b.checked = !allOn; });
        });
    });
</script>
@endsection