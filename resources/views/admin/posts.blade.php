@extends('layouts.panel')

@section('title', 'All Posts · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">All Posts</h3>
    <p class="muted mb-0">Review pending posts, or filter, edit and remove any post.</p>
</div>

<div class="card-x p-3 p-md-4">
    @php
        $tabs = [
            ''         => ['All', $counts->sum()],
            'pending'  => ['Pending', $counts['pending'] ?? 0],
            'approved' => ['Approved', $counts['approved'] ?? 0],
            'rejected' => ['Rejected', $counts['rejected'] ?? 0],
        ];
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($tabs as $key => [$label, $n])
            <a href="{{ route('admin.posts.index', array_merge(request()->except('status', 'page'), $key !== '' ? ['status' => $key] : [])) }}"
               class="chip {{ (string) request('status') === (string) $key ? 'active' : '' }}">
                {{ $label }}<small>{{ $n }}</small>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.posts.index') }}" class="row g-2 mb-4">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="col-md-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search title...">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected((string) request('category') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="author" class="form-select">
                <option value="">All authors</option>
                @foreach($authors as $a)
                    <option value="{{ $a->id }}" @selected((string) request('author') === (string) $a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-dash flex-fill">Filter</button>
            @if(request()->hasAny(['q', 'category', 'author', 'status']))
                <a href="{{ route('admin.posts.index') }}" class="btn btn-nav-outline"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-x table-hover align-middle mb-0">
            <thead><tr><th>Post</th><th>Author</th><th>Category</th><th>Status</th><th>Views</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($posts as $post)
                    @php $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$post->status] ?? 'secondary'; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="row-thumb" @if($post->image) style="background-image:url('{{ $post->image_url }}')" @endif></div>
                                <div>
                                    <div class="fw-semibold">{{ Str::limit($post->title, 40) }}</div>
                                    <small class="muted">{{ $post->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="muted">{{ $post->user->name }}</td>
                        <td><span class="badge-cat">{{ $post->category->name }}</span></td>
                        <td>
                            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($post->status) }}</span>
                            @if($post->status === 'rejected' && $post->rejection_reason)
                                <div class="small muted mt-1" title="{{ $post->rejection_reason }}">{{ Str::limit($post->rejection_reason, 30) }}</div>
                            @endif
                        </td>
                        <td class="muted">{{ $post->views }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('posts.show', $post) }}" class="btn-icon" title="Preview" target="_blank"><i class="bi bi-eye"></i></a>

                            @if($post->status !== 'approved')
                                <form method="POST" action="{{ route('admin.posts.approve', $post) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn-icon" style="color:#16a34a" title="Approve"><i class="bi bi-check-circle"></i></button>
                                </form>
                            @endif

                            @if($post->status !== 'rejected')
                                <button type="button" class="btn-icon danger js-reject" title="Reject"
                                        data-action="{{ route('admin.posts.reject', $post) }}"
                                        data-title="{{ $post->title }}">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif

                            <a href="{{ route('dashboard.posts.edit', $post) }}" class="btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="d-inline" onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 muted">No posts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $posts->links() }}</div>
    @endif
</div>

{{-- Reject modal --}}

<div class="modal fade reject-modal" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="rejectForm" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header align-items-start gap-3">
                <div class="reject-icon"><i class="bi bi-x-octagon"></i></div>
                <div class="flex-grow-1">
                    <h5 class="modal-title fw-bold mb-1" id="rejectModalLabel">Reject this post?</h5>
                    <div class="small muted">The author will be notified with your reason.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="reject-post mb-3 text-truncate" id="rejectPostTitle"></div>
                <div class="d-flex justify-content-between">
                    <label class="form-label small fw-semibold" for="rejectReason">Reason <span class="muted fw-normal">(optional)</span></label>
                    <span class="small muted" id="rejectCount">0 / 255</span>
                </div>
                <textarea name="reason" id="rejectReason" rows="3" maxlength="255" class="form-control" placeholder="Tell the author what to fix..."></textarea>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" class="reason-chip">Low quality content</button>
                    <button type="button" class="reason-chip">Inappropriate content</button>
                    <button type="button" class="reason-chip">Needs more details</button>
                    <button type="button" class="reason-chip">Duplicate post</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-nav-outline px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-reject"><i class="bi bi-x-circle me-1"></i>Reject post</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('rejectModal');
        const modal = new bootstrap.Modal(el);
        const form = document.getElementById('rejectForm');
        const reason = document.getElementById('rejectReason');
        const count = document.getElementById('rejectCount');
        const updateCount = () => { count.textContent = reason.value.length + ' / 255'; };

        document.querySelectorAll('.js-reject').forEach(btn => {
            btn.addEventListener('click', () => {
                form.action = btn.dataset.action;
                document.getElementById('rejectPostTitle').textContent = btn.dataset.title;
                reason.value = '';
                updateCount();
                modal.show();
            });
        });

        document.querySelectorAll('.reason-chip').forEach(chip => {
            chip.addEventListener('click', () => { reason.value = chip.textContent; updateCount(); reason.focus(); });
        });

        reason.addEventListener('input', updateCount);
        el.addEventListener('shown.bs.modal', () => reason.focus());
    });
</script>
@endsection