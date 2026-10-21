@extends('layouts.panel')

@section('title', 'Categories · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Categories</h3>
    <p class="muted mb-0">Add, rename or remove categories.</p>
</div>

<div class="card-x p-4 mb-4">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="d-flex gap-2">
        @csrf
        <input type="text" name="name" class="form-control" placeholder="New category name" required>
        <button class="btn btn-dash text-nowrap"><i class="bi bi-plus-lg me-1"></i>Add</button>
    </form>
</div>

<div class="card-x p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-x align-middle mb-0">
            <thead><tr><th>Name</th><th>Posts</th><th class="text-end">Delete</th></tr></thead>
            <tbody>
                @foreach($categories as $c)
                    <tr>
                        <td style="min-width:260px">
                            <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="d-flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm" required>
                                <button class="btn-icon" title="Save"><i class="bi bi-check-lg"></i></button>
                            </form>
                        </td>
                        <td class="muted">{{ $c->posts_count }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection