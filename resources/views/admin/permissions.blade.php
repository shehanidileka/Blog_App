@extends('layouts.panel')

@section('title', 'Permissions · Admin')

@section('panel')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Permissions</h3>
        <p class="muted mb-0">Add, edit or delete permissions, then give them to roles.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-nav-outline"><i class="bi bi-shield-check me-1"></i>Roles</a>
</div>

{{-- ===== Add new ===== --}}
<div class="card-x p-4 mb-4">
    <h6 class="mb-3">New permission</h6>
    <form method="POST" action="{{ route('admin.permissions.store') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
            <label class="form-label" for="p-name">Name</label>
            <input type="text" id="p-name" name="name" class="form-control" required maxlength="80"
                   placeholder="e.g. Publish posts" value="{{ old('name') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="p-slug">Slug</label>
            <input type="text" id="p-slug" name="slug" class="form-control" required maxlength="80"
                   placeholder="e.g. posts.publish" value="{{ old('slug') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="p-group">Group</label>
            <input type="text" id="p-group" name="group" class="form-control" required maxlength="50" list="groupList"
                   placeholder="e.g. Posts" value="{{ old('group') }}">
            <datalist id="groupList">
                @foreach($allGroups as $g)<option value="{{ $g }}">@endforeach
            </datalist>
        </div>
        <div class="col-12">
            <label class="form-label" for="p-desc">Description</label>
            <input type="text" id="p-desc" name="description" class="form-control" maxlength="255"
                   placeholder="What does this permission allow?" value="{{ old('description') }}">
        </div>
        <div class="col-12">
            <button class="btn btn-dash"><i class="bi bi-plus-lg me-1"></i>Add permission</button>
        </div>
    </form>
    <p class="muted small mt-3 mb-0">
        Use it in code with <code>@@can('slug')</code> or <code>middleware('can:slug')</code>.
        A new permission has no effect until you tick it for a role.
    </p>
</div>

{{-- ===== Existing, by group ===== --}}
@foreach($groups as $group => $perms)
    <div class="card-x p-3 p-md-4 mb-4">
        <h6 class="mb-3">{{ $group }}</h6>
        <div class="table-responsive">
            <table class="table table-x align-middle mb-0">
                <thead><tr><th>Permission</th><th>Slug</th><th>Roles</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($perms as $p)
                        @php $locked = in_array($p->slug, $builtIn, true); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $p->name }}
                                    @if($locked)<span class="role-badge user ms-1">Built-in</span>@endif
                                </div>
                                <small class="muted">{{ $p->description }}</small>
                            </td>
                            <td><code>{{ $p->slug }}</code></td>
                            <td class="muted">{{ $p->roles_count }}</td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn-icon" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editPerm"
                                        data-action="{{ route('admin.permissions.update', $p) }}"
                                        data-name="{{ $p->name }}" data-slug="{{ $p->slug }}"
                                        data-group="{{ $p->group }}" data-description="{{ $p->description }}"
                                        data-locked="{{ $locked ? 1 : 0 }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @unless($locked)
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $p) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this permission? It will be removed from all roles.')">
                                        @csrf @method('DELETE')
                                        <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

{{-- ===== Edit modal ===== --}}
<div class="modal fade" id="editPerm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content card-x" id="editPermForm">
            @csrf @method('PUT')
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Edit permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="e-name">Name</label>
                    <input type="text" id="e-name" name="name" class="form-control" required maxlength="80">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="e-slug">Slug</label>
                    <input type="text" id="e-slug" name="slug" class="form-control" required maxlength="80">
                    <div class="muted small mt-1 d-none" id="e-slug-note">Built-in slugs are used by the application and cannot be changed.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="e-group">Group</label>
                    <input type="text" id="e-group" name="group" class="form-control" required maxlength="50" list="groupList">
                </div>
                <div>
                    <label class="form-label" for="e-desc">Description</label>
                    <input type="text" id="e-desc" name="description" class="form-control" maxlength="255">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-nav-outline" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-dash">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('editPerm').addEventListener('show.bs.modal', function (e) {
        var b = e.relatedTarget, f = document.getElementById('editPermForm');
        var locked = b.dataset.locked === '1';
        f.action = b.dataset.action;
        document.getElementById('e-name').value  = b.dataset.name;
        document.getElementById('e-group').value = b.dataset.group;
        document.getElementById('e-desc').value  = b.dataset.description || '';
        var slug = document.getElementById('e-slug');
        slug.value = b.dataset.slug;
        slug.disabled = locked;               // disabled fields are not submitted; the server ignores slug for built-ins
        document.getElementById('e-slug-note').classList.toggle('d-none', !locked);
    });
</script>
@endsection