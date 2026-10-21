@extends('layouts.panel')

@section('title', 'My Posts · MyBlog')

@section('panel')

@php $first = \Illuminate\Support\Str::before(auth()->user()->name, ' '); @endphp

{{-- Hero --}}
<div class="dash-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="mb-1">Hi {{ $first }} 👋</h3>
            <p>Manage your stories, track views and publish something new.</p>
        </div>
        @can('posts.create')
            <a href="{{ route('dashboard.posts.create') }}" class="btn btn-new"><i class="bi bi-plus-circle me-1"></i>New post</a>
        @endcan
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4 stat-card-col">
        <div class="card-x stat stat-card">
            <div class="stat-icon"><i class="bi bi-journal-richtext"></i></div>
            <div><div class="stat-num">{{ number_format($stats['posts']) }}</div><small class="muted">My posts</small></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-x stat stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#f97316)"><i class="bi bi-eye"></i></div>
            <div><div class="stat-num">{{ number_format($stats['views']) }}</div><small class="muted">Total views</small></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-x stat stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#6366f1)"><i class="bi bi-grid"></i></div>
            <div><div class="stat-num">{{ number_format($stats['categories']) }}</div><small class="muted">Categories used</small></div>
        </div>
    </div>
</div>

{{-- Posts --}}
<div class="card-x p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="fw-bold mb-0">My Posts</h5>
        <span class="muted small">{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}</span>
    </div>

    {{-- Category chips --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('dashboard', request()->only('q')) }}" class="chip {{ ! request('category') ? 'active' : '' }}">All<small>{{ $stats['posts'] }}</small></a>
        @foreach($categories->where('my_posts_count', '>', 0) as $c)
            <a href="{{ route('dashboard', array_filter(['category' => $c->id, 'q' => request('q')])) }}"
               class="chip {{ (string) request('category') === (string) $c->id ? 'active' : '' }}">{{ $c->name }}<small>{{ $c->my_posts_count }}</small></a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 mb-4">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <div class="col-md-9">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search my posts by title...">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-dash flex-fill"><i class="bi bi-search me-1"></i>Search</button>
            @if(request()->hasAny(['q', 'category']))
                <a href="{{ route('dashboard') }}" class="btn btn-nav-outline" title="Clear"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-x table-hover align-middle mb-0">
            <thead>
                <tr><th>Post</th><th>Category</th><th>Status</th><th>Views</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    @php
                        $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary'][$post->status] ?? 'secondary';
                    @endphp
                    <tr class="post-row">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="row-thumb" @if($post->image) style="background-image:url('{{ $post->image_url }}')" @endif></div>
                                <div style="min-width:0">
                                    @if($post->status === 'approved')
                                        <a href="{{ route('posts.show', $post) }}" class="ptitle">{{ \Illuminate\Support\Str::limit($post->title, 45) }}</a>
                                    @else
                                        <span class="ptitle">{{ \Illuminate\Support\Str::limit($post->title, 45) }}</span>
                                    @endif
                                    <div><small class="muted">{{ $post->created_at->format('M d, Y') }}</small></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge-cat">{{ $post->category->name ?? '—' }}</span></td>
                        <td>
                            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($post->status) }}</span>
                            @if($post->status === 'rejected' && $post->rejection_reason)
                                <div class="small muted mt-1" title="{{ $post->rejection_reason }}">{{ \Illuminate\Support\Str::limit($post->rejection_reason, 32) }}</div>
                            @endif
                        </td>
                        <td class="muted">{{ number_format($post->views) }}</td>
                        <td class="text-end text-nowrap">
                            @if($post->status === 'approved')
                                <a href="{{ route('posts.show', $post) }}" class="btn-icon" title="View" target="_blank"><i class="bi bi-eye"></i></a>
                            @endif
                            <a href="{{ route('dashboard.posts.edit', $post) }}" class="btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this post? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="ico"><i class="bi bi-pencil-square"></i></div>
                                @if(request()->hasAny(['q', 'category']))
                                    <h6 class="fw-bold">No matching posts</h6>
                                    <p class="muted small mb-3">Try a different search or clear the filters.</p>
                                    <a href="{{ route('dashboard') }}" class="btn btn-nav-outline">Clear filters</a>
                                @else
                                    <h6 class="fw-bold">You haven't written anything yet</h6>
                                    <p class="muted small mb-3">Share your first story with the community.</p>
                                    @can('posts.create')
                                        <a href="{{ route('dashboard.posts.create') }}" class="btn btn-dash"><i class="bi bi-plus-circle me-1"></i>Write your first post</a>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $posts->links() }}</div>
    @endif
</div>
@endsection