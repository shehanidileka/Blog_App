@extends('layouts.app')

@section('title', 'Notifications · MyBlog')

@section('content')
<div class="mx-auto" style="max-width:760px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Notifications</h3>
            <p class="muted mb-0">Updates about your posts.</p>
        </div>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="btn btn-nav-outline btn-sm">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="card-x">
        @forelse($notifications as $n)
            <a href="{{ route('notifications.open', $n->id) }}" class="notif-item {{ $n->read_at ? '' : 'unread' }}">
                @if(($n->data['status'] ?? '') === 'approved')
                    <i class="bi bi-check-circle-fill text-success"></i>
                @else
                    <i class="bi bi-x-circle-fill text-danger"></i>
                @endif
                <div class="flex-grow-1">
                    <div>{{ $n->data['message'] ?? 'Notification' }}</div>
                    @if(! empty($n->data['reason']))
                        <div class="small muted mt-1">Reason: {{ $n->data['reason'] }}</div>
                    @endif
                    <small class="muted">{{ $n->created_at->diffForHumans() }}</small>
                </div>
                @unless($n->read_at)<span class="badge text-bg-success align-self-start">New</span>@endunless
            </a>
        @empty
            <div class="text-center py-5 muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                No notifications yet.
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection