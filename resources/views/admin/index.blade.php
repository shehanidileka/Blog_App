@extends('layouts.panel')

@section('title', 'Admin Panel · MyBlog')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Admin Overview</h3>
    <p class="muted mb-0">Everything happening on MyBlog.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card-x stat"><div class="stat-icon"><i class="bi bi-people"></i></div>
            <div><div class="stat-num">{{ $stats['users'] }}</div><small class="muted">Users</small></div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card-x stat"><div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div><div class="stat-num">{{ $stats['posts'] }}</div><small class="muted">Published posts</small></div></div>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.posts.index', ['status' => 'pending']) }}" class="card-x stat text-decoration-none" style="color:inherit">
            <div class="stat-icon" style="background:#f59e0b"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="stat-num">{{ $stats['pending'] }}</div><small class="muted">Pending approval</small></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card-x stat"><div class="stat-icon"><i class="bi bi-eye"></i></div>
            <div><div class="stat-num">{{ number_format($stats['views']) }}</div><small class="muted">Views</small></div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card-x p-4 h-100">
            <h6 class="mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Published posts by category</h6>
            @foreach($byCategory as $c)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $c->name }}</span><span class="muted">{{ $c->posts_count }}</span>
                    </div>
                    <div class="bar"><span style="width: {{ ($c->posts_count / $max) * 100 }}%"></span></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-x p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>New users</h6>
                <a href="{{ route('admin.users.index') }}" class="small text-decoration-none">View all</a>
            </div>
            @foreach($latestUsers as $u)
                <div class="d-flex align-items-center gap-3 py-2" style="border-top:1px solid var(--border)">
                    @if($u->avatar)
                        <img src="{{ $u->avatar }}" class="avatar" alt="" referrerpolicy="no-referrer">
                    @else
                        <span class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                    @endif
                    <div style="min-width:0">
                        <div class="small fw-semibold text-truncate">{{ $u->name }}</div>
                        <small class="muted">{{ $u->created_at->diffForHumans() }}</small>
                    </div>
                    <span class="role-badge {{ $u->role }} ms-auto">{{ $u->role }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card-x p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Latest posts</h6>
        <a href="{{ route('admin.posts.index') }}" class="small text-decoration-none">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-x align-middle mb-0">
            <thead><tr><th>Post</th><th>Author</th><th>Category</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($latestPosts as $p)
                    @php $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$p->status] ?? 'secondary'; @endphp
                    <tr>
                        <td class="fw-semibold">{{ Str::limit($p->title, 40) }}</td>
                        <td class="muted">{{ $p->user->name }}</td>
                        <td><span class="badge-cat">{{ $p->category->name }}</span></td>
                        <td><span class="badge text-bg-{{ $badge }}">{{ ucfirst($p->status) }}</span></td>
                        <td class="muted">{{ $p->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection