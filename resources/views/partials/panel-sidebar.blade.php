{{-- ===== DASHBOARD / ADMIN SIDEBAR ===== --}}
<aside class="card-x p-3 panel-side">
    <div class="d-flex align-items-center gap-3 p-2 mb-1">
        @if(auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar }}" class="avatar" alt="" referrerpolicy="no-referrer">
        @else
            <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        @endif
        <div style="min-width:0">
            <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
            <small class="muted d-block text-truncate">{{ auth()->user()->email }}</small>
        </div>
    </div>

    @if(request()->routeIs('admin.*'))
        {{-- ===== ADMIN MENU ===== --}}
        <div class="side-label">Admin</div>
        <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>Overview</a>

        @can('posts.manage')
            @php $pendingCount = \App\Models\Post::pending()->count(); @endphp

            <a class="side-link {{ request()->routeIs('admin.posts.*') && request('status') !== 'pending' ? 'active' : '' }}" href="{{ route('admin.posts.index') }}"><i class="bi bi-file-earmark-text"></i>All Posts</a>

            <a class="side-link {{ request()->routeIs('admin.posts.*') && request('status') === 'pending' ? 'active' : '' }}" href="{{ route('admin.posts.index', ['status' => 'pending']) }}">
                <i class="bi bi-hourglass-split"></i>Pending Approval
                @if($pendingCount > 0)
                    <span class="badge text-bg-warning ms-auto">{{ $pendingCount }}</span>
                @endif
            </a>
        @endcan

        @can('categories.manage')
            <a class="side-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i>Categories</a>
        @endcan
        @can('users.manage')
            <a class="side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i>Users</a>
        @endcan
        @can('roles.manage')
            <a class="side-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-check"></i>Roles & Permissions</a>
            <a class="side-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"><i class="bi bi-key"></i>Permissions</a>
        @endcan
        @can('activity.view')
            <a class="side-link {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}" href="{{ route('admin.activity.index') }}"><i class="bi bi-clock-history"></i>Activity Log</a>
        @endcan
        @can('settings.manage')
            <a class="side-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.mail') }}"><i class="bi bi-envelope-at"></i>Mail Settings</a>
            <a class="side-link {{ request()->routeIs('admin.api-tokens.*') ? 'active' : '' }}" href="{{ route('admin.api-tokens.index') }}"><i class="bi bi-braces"></i>API Tokens</a>
        @endcan

        <div class="side-label">Account</div>
        <a class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>My Profile</a>
        <a class="side-link" href="{{ route('dashboard') }}"><i class="bi bi-person-workspace"></i>My Dashboard</a>
    @else
        {{-- ===== USER MENU ===== --}}
        <div class="side-label">Menu</div>
        <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i>My Posts</a>
        @can('posts.create')
            <a class="side-link {{ request()->routeIs('dashboard.posts.create') ? 'active' : '' }}" href="{{ route('dashboard.posts.create') }}"><i class="bi bi-plus-circle"></i>New Post</a>
        @endcan

        <a class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>My Profile</a>

        @php $unreadNotifs = auth()->user()->unreadNotifications()->count(); @endphp
        <a class="side-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
            <i class="bi bi-bell"></i>Notifications
            @if($unreadNotifs > 0)
                <span class="badge text-bg-danger ms-auto">{{ $unreadNotifs }}</span>
            @endif
        </a>

        @can('admin.access')
            <div class="side-label">Admin</div>
            <a class="side-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock"></i>Admin Panel</a>
        @endcan
        <div class="side-label">Account</div>
    @endif

    <a class="side-link" href="{{ route('home') }}"><i class="bi bi-house"></i>Back to site</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="side-link danger"><i class="bi bi-box-arrow-right"></i>Logout</button>
    </form>
</aside>