@extends('layouts.app')

@section('content')

{{-- INTRO --}}
<section class="intro">
    <div class="reveal">
        <span class="intro-pill"><span class="pulse"></span>{{ $stats['posts'] }} stories and growing</span>
        <h1>Stories, ideas &amp; tutorials <span class="grad">worth your time.</span></h1>
        <p class="lead">Fresh articles from our community of writers. Discover something new, join the conversation, or publish your own post.</p>
        <div class="intro-cta">
            <a href="{{ route('posts.index') }}" class="btn btn-dash"><i class="bi bi-compass me-1"></i>Explore posts</a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-nav-outline">Start writing</a>
            @else
                @can('posts.create')
                    <a href="{{ route('dashboard.posts.create') }}" class="btn btn-nav-outline"><i class="bi bi-pencil-square me-1"></i>Write a post</a>
                @endcan
            @endguest
        </div>
    </div>
    <div class="stat-grid">
        <div class="stat reveal"><i class="bi bi-journal-richtext"></i><b data-count="{{ $stats['posts'] }}">0</b><span>Published posts</span></div>
        <div class="stat reveal"><i class="bi bi-people"></i><b data-count="{{ $stats['writers'] }}">0</b><span>Active writers</span></div>
        <div class="stat reveal"><i class="bi bi-grid"></i><b data-count="{{ $stats['categories'] }}">0</b><span>Categories</span></div>
        <div class="stat reveal"><i class="bi bi-eye"></i><b data-count="{{ $stats['views'] }}">0</b><span>Total views</span></div>
    </div>
</section>

<div class="row g-4">

    {{-- LEFT --}}
    <div class="col-lg-8">
        @if($featured)
        <article class="feature spot reveal">
            <div class="feature-img" @if($featured->image) style="background-image:url('{{ asset('storage/'.$featured->image) }}')" @endif>
                <span class="feature-tag"><i class="bi bi-star-fill"></i>Featured</span>
            </div>
            <div class="feature-body">
                <div class="home-eyebrow">
                    <span>{{ $featured->category->name }}</span><span class="dot"></span>
                    <span class="sub">{{ max(1, ceil(str_word_count(strip_tags($featured->body)) / 200)) }} min read</span>
                </div>
                <h2 class="feature-title">{{ $featured->title }}</h2>
                <p class="feature-excerpt">{{ Str::limit(strip_tags($featured->excerpt ?: $featured->body), 150) }}</p>
                <div class="feature-foot">
                    <div class="byline">
                        <span class="mini-av">{{ Str::upper(Str::substr($featured->user->name, 0, 1)) }}</span>
                        <span class="who"><b class="text-truncate">{{ $featured->user->name }}</b><small>{{ $featured->created_at->format('M d, Y') }}</small></span>
                        <span class="stats">
                            <span><i class="bi bi-heart me-1"></i>{{ $featured->reactions_count }}</span>
                            <span><i class="bi bi-chat-dots me-1"></i>{{ $featured->comments_count }}</span>
                        </span>
                    </div>
                    <a href="{{ route('posts.show', $featured) }}" class="feature-cta">Read article <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </article>
        @endif

        <div class="sec-head">
            <div>
                <h2>Latest posts</h2>
                <p>Fresh stories from our writers</p>
            </div>
            <a href="{{ route('posts.index') }}">View all <i class="bi bi-arrow-right"></i></a>
        </div>

        @php $chipCats = $latest->pluck('category')->unique('id')->values(); @endphp
        @if($chipCats->count() > 1)
        <div class="chips" id="chips">
            <button type="button" class="chip active" data-filter="all">All</button>
            @foreach($chipCats as $cc)
                <button type="button" class="chip" data-filter="{{ $cc->slug }}">{{ $cc->name }}</button>
            @endforeach
        </div>
        @endif

        <div class="row g-4" id="postGrid">
            @forelse($latest as $post)
            <div class="col-sm-6 post-col reveal" data-cat="{{ $post->category->slug }}">
                <a href="{{ route('posts.show', $post) }}" class="card-x post-card home-card spot d-block text-decoration-none" style="color:inherit">
                    <div class="post-img" @if($post->image) style="background-image:url('{{ asset('storage/'.$post->image) }}')" @endif></div>
                    <div class="p-3">
                        <div class="home-eyebrow">
                            <span>{{ $post->category->name }}</span><span class="dot"></span>
                            <span class="sub">{{ max(1, ceil(str_word_count(strip_tags($post->body)) / 200)) }} min read</span>
                        </div>
                        <h6>{{ $post->title }}</h6>
                        <p class="post-excerpt">{{ Str::limit(strip_tags($post->excerpt ?: $post->body), 95) }}</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2 min-w-0">
                                <span class="mini-av">{{ Str::upper(Str::substr($post->user->name, 0, 1)) }}</span>
                                <span class="d-flex flex-column lh-sm">
                                    <span class="by text-truncate">{{ $post->user->name }}</span>
                                    <span class="when">{{ $post->created_at->format('M d, Y') }}</span>
                                </span>
                            </span>
                            <small class="muted d-flex gap-3">
                                <span title="Reactions"><i class="bi bi-heart me-1"></i>{{ $post->reactions_count }}</span>
                                <span title="Comments"><i class="bi bi-chat-dots me-1"></i>{{ $post->comments_count }}</span>
                            </small>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <p class="muted">No posts yet.</p>
            @endforelse
        </div>
        <p id="noMatch" class="muted text-center py-4"><i class="bi bi-search me-1"></i>No posts in this category yet.</p>
    </div>

    {{-- RIGHT --}}
    <div class="col-lg-4">
        <div class="card-x side-card spot reveal mb-4">
            <h5 class="side-head">Popular <small>Top {{ $popular->count() }}</small></h5>
            @foreach($popular as $i => $p)
            <a href="{{ route('posts.show', $p) }}" class="pop-row">
                <span class="pop-num">{{ sprintf('%02d', $i + 1) }}</span>
                <div class="min-w-0">
                    <div class="pop-title">{{ $p->title }}</div>
                    <small><i class="bi bi-eye me-1"></i>{{ $p->views }} views</small>
                </div>
                <div class="thumb" @if($p->image) style="background-image:url('{{ asset('storage/'.$p->image) }}')" @endif></div>
            </a>
            @endforeach
        </div>

        <div class="card-x side-card spot reveal mb-4">
            <h5 class="side-head">Categories</h5>
            @foreach($categories as $c)
            <a href="{{ route('categories.show', $c->slug) }}" class="cat-row">
                <span>{{ $c->name }}</span><em>{{ $c->posts_count }}</em>
            </a>
            @endforeach
        </div>

        @guest
        <div class="cta-card reveal">
            <i class="bi bi-pencil-square fs-4"></i>
            <h5>Share your story</h5>
            <p>Join MyBlog and publish your own posts. It only takes a minute to get started.</p>
            <a href="{{ route('register') }}" class="btn">Create an account</a>
        </div>
        @else
            @can('posts.create')
            <div class="cta-card reveal">
                <i class="bi bi-pencil-square fs-4"></i>
                <h5>Got something to say?</h5>
                <p>Write a new post and share it with the community.</p>
                <a href="{{ route('dashboard.posts.create') }}" class="btn">Write a post</a>
            </div>
            @endcan
        @endguest
    </div>

</div>

<script>
(function () {
    // Count-up numbers
    const fmt = n => n >= 10000 ? (n / 1000).toFixed(n >= 100000 ? 0 : 1).replace('.0','') + 'k' : n.toLocaleString();
    const cio = new IntersectionObserver((es) => es.forEach(e => {
        if (!e.isIntersecting) return;
        const el = e.target, to = +el.dataset.count, t0 = performance.now(), dur = 1300;
        const tick = (t) => {
            const k = Math.min((t - t0) / dur, 1), v = Math.round(to * (1 - Math.pow(1 - k, 3)));
            el.textContent = fmt(v);
            if (k < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        cio.unobserve(el);
    }), { threshold: .5 });
    document.querySelectorAll('[data-count]').forEach(el => cio.observe(el));

    // Category filter chips
    const chips = document.getElementById('chips');
    if (chips) {
        const cols = [...document.querySelectorAll('#postGrid .post-col')], none = document.getElementById('noMatch');
        chips.addEventListener('click', (e) => {
            const b = e.target.closest('.chip'); if (!b) return;
            chips.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === b));
            let shown = 0;
            cols.forEach(c => {
                const ok = b.dataset.filter === 'all' || c.dataset.cat === b.dataset.filter;
                c.classList.toggle('hide', !ok);
                if (ok) { shown++; c.classList.add('in'); c.classList.remove('pop'); void c.offsetWidth; c.classList.add('pop'); }
            });
            none.style.display = shown ? 'none' : 'block';
        });
    }
})();
</script>
@endsection