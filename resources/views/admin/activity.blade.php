@extends('layouts.panel')

@section('title', 'Activity Log · Admin')

@section('panel')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Activity Log</h3>
    <p class="muted mb-0">See who did what, and when.</p>
</div>

<div class="card-x p-3 p-md-4 mb-4">
    <form method="GET" action="{{ route('admin.activity.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1" for="q">Search</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="User, text or IP...">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="type">Type</label>
            <select id="type" name="type" class="form-select">
                <option value="">All</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="from">From</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="to">To</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-dash flex-grow-1"><i class="bi bi-funnel"></i></button>
            @if(request()->hasAny(['q','type','from','to']))
                <a href="{{ route('admin.activity.index') }}" class="btn btn-nav-outline">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="card-x p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-x table-hover align-middle mb-0">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap">
                            <div>{{ $log->created_at->format('M d, Y') }}</div>
                            <small class="muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>{{ $log->user_name ?? 'Guest / system' }}</td>
                        <td><span class="badge text-bg-{{ $log->tone }}">{{ str_replace(['.', '_'], ' ', $log->action) }}</span></td>
                        <td style="min-width:260px">
                            {{ $log->description }}
                            @if($log->properties)
                                <details class="mt-1">
                                    <summary class="small muted" style="cursor:pointer">Changes</summary>
                                    <ul class="small mb-0 mt-1 ps-3">
                                        @foreach($log->properties as $field => $v)
                                            <li>
                                                <b>{{ $field }}</b>:
                                                <span class="muted">{{ $v['old'] ?? '—' }}</span> → {{ $v['new'] ?? '—' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                        <td class="muted small text-nowrap">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center muted py-4">No activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
</div>

<div class="card-x p-3 p-md-4 mt-4">
    <form method="POST" action="{{ route('admin.activity.prune') }}" class="d-flex flex-wrap align-items-center gap-2"
          onsubmit="return confirm('Delete the old log entries? This cannot be undone.')">
        @csrf @method('DELETE')
        <span class="muted small">Delete entries older than</span>
        <input type="number" name="days" value="90" min="1" max="3650" class="form-control form-control-sm" style="width:90px">
        <span class="muted small">days</span>
        <button class="btn btn-nav-outline btn-sm"><i class="bi bi-trash me-1"></i>Clean up</button>
    </form>
</div>
@endsection