
<nav class="navbar navbar-expand-lg topbar sticky-top" id="topbar">
    <div class="container">
        <x-logo />

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-lg-4 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-x {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-x dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                    <ul class="dropdown-menu" style="min-width:220px">
                        <li><a class="dropdown-item" href="{{ route('posts.index') }}"><i class="bi bi-collection me-2"></i>All Posts</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @forelse($navCategories ?? [] as $c)
                            <li><a class="dropdown-item" href="{{ route('categories.show', $c->slug) }}"><i class="bi bi-folder me-2"></i>{{ $c->name }}</a></li>
                        @empty
                            <li><span class="dropdown-item-text muted">No categories yet</span></li>
                        @endforelse
                    </ul>
                </li>
            </ul>

            <form class="search-box mx-lg-auto my-3 my-lg-0" action="{{ route('posts.index') }}" method="GET">
                <i class="bi bi-search s-icon"></i>
                <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control" placeholder="Search posts..." autocomplete="off">
                <kbd class="d-none d-lg-inline">/</kbd>
            </form>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button id="themeBtn" class="icon-btn" title="Toggle theme">
                    <i class="bi bi-sun icon-sun"></i><i class="bi bi-moon-stars icon-moon"></i>
                </button>

                @guest
                    <a href="{{ route('login') }}" class="btn btn-nav-outline">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-dash">Register</a>
                @endguest

                @auth
                    {{-- Notifications --}}
                    <div class="dropdown">
                        <button type="button" class="icon-btn position-relative" data-bs-toggle="dropdown" title="Notifications" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            @if(($unreadCount ?? 0) > 0)
                                <span class="notif-dot">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0" style="width:340px;max-width:92vw">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="border-color:var(--border)!important">
                                <strong class="small">Notifications</strong>
                                @if(($unreadCount ?? 0) > 0)
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            @forelse($navNotifications ?? [] as $n)
                                <a href="{{ route('notifications.open', $n->id) }}" class="notif-item {{ $n->read_at ? '' : 'unread' }}">
                                    @if(($n->data['status'] ?? '') === 'approved')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    @endif
                                    <div>
                                        <div class="small">{{ $n->data['message'] ?? 'Notification' }}</div>
                                        <small class="muted">{{ $n->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center muted small py-4">No notifications yet</div>
                            @endforelse
                            <a href="{{ route('notifications.index') }}" class="d-block text-center small fw-semibold py-2 text-decoration-none border-top" style="border-color:var(--border)!important">View all</a>
                        </div>
                    </div>

                    {{-- Profile menu --}}
                    <div class="dropdown">
                        <a href="#" class="profile-btn d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" class="avatar" alt="" referrerpolicy="no-referrer">
                            @else
                                <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="d-none d-xl-inline">{{ Str::before(auth()->user()->name, ' ') }}</span>
                            <i class="bi bi-chevron-down small me-1"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:240px">
                            <li class="px-3 py-2">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <small class="muted">{{ auth()->user()->email }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            @if(auth()->user()->can('admin.access'))
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>