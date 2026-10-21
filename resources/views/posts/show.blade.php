@extends('layouts.app')

@section('title', $post->seo_title . ' · MyBlog')

@push('meta')
    <meta name="description" content="{{ $post->seo_description }}">
    @if($post->meta_keywords)
        <meta name="keywords" content="{{ $post->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ route('posts.show', $post) }}">

    {{-- Open Graph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="MyBlog">
    <meta property="og:title" content="{{ $post->seo_title }}">
    <meta property="og:description" content="{{ $post->seo_description }}">
    <meta property="og:url" content="{{ route('posts.show', $post) }}">
    @if($post->seo_image)
        <meta property="og:image" content="{{ $post->seo_image }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $post->seo_image ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $post->seo_title }}">
    <meta name="twitter:description" content="{{ $post->seo_description }}">
    @if($post->seo_image)
        <meta name="twitter:image" content="{{ $post->seo_image }}">
    @endif

@endpush

@section('content')
@php
    $mins   = max(1, ceil(str_word_count(strip_tags($post->body)) / 200));
    $url    = urlencode(route('posts.show', $post));
    $ttl    = urlencode($post->title);
    $rc     = $reactionCounts->sum();
    $author = $post->user;
@endphp

<div class="post-wrap">

    @if(! $post->isApproved())
        <div class="alert alert-warning rounded-3">
            <i class="bi bi-hourglass-split me-1"></i>
            This post is <strong>{{ $post->status }}</strong> and is not visible to the public.
        </div>
    @endif

    {{-- Breadcrumb --}}
    <nav class="crumbs reveal" aria-label="breadcrumb">
        <a href="{{ route('home') }}"><i class="bi bi-house me-1"></i>Home</a>
        <i class="bi bi-chevron-right sep"></i>
        <a href="{{ route('categories.show', $post->category->slug) }}">{{ $post->category->name }}</a>
        <i class="bi bi-chevron-right sep"></i>
        <span class="cur">{{ $post->title }}</span>
    </nav>

    {{-- Header --}}
    <header class="post-head reveal">
        <a href="{{ route('categories.show', $post->category->slug) }}" class="post-cat">
            <i class="bi bi-hash"></i>{{ $post->category->name }}
        </a>

        <h1 class="post-title">{{ $post->title }}</h1>

        @if($post->excerpt)
            <p class="post-lead">{{ $post->excerpt }}</p>
        @endif

        <div class="byline-row">
            <div class="by-author">
                @if($author->avatar)
                    <img src="{{ $author->avatar }}" alt="" referrerpolicy="no-referrer">
                @else
                    <span class="avatar">{{ strtoupper(substr($author->name, 0, 1)) }}</span>
                @endif
                <div>
                    <b>{{ $author->name }}</b>
                    <small>{{ $post->created_at->format('M d, Y') }} · {{ $mins }} min read</small>
                </div>
            </div>

            <div class="by-stats">
                <span class="by-stat"><i class="bi bi-eye"></i>{{ number_format($post->views) }} {{ Str::plural('view', $post->views) }}</span>
                <a href="#reactions" class="by-stat"><i class="bi bi-heart"></i>{{ $rc }}</a>
                <a href="#comments" class="by-stat"><i class="bi bi-chat-dots"></i>{{ $comments->count() }}</a>
            </div>
        </div>
    </header>

    {{-- Cover --}}
    @if($post->image)
        <figure class="post-cover reveal">
            <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->image_alt ?: $post->title }}">
        </figure>
    @endif

    <div class="row g-4 g-xl-5">

        {{-- MAIN --}}
        <div class="col-lg-8">

            <article class="card-x article-card reveal">
                <div class="post-body" id="postBody">
                    {!! $post->body_html !!}
                </div>

                @if($post->tags->isNotEmpty())
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-5 pt-4" style="border-top:1px solid var(--border)">
                        <i class="bi bi-tags muted me-1"></i>
                        @foreach($post->tags as $t)
                            <a href="{{ route('tags.show', $t->slug) }}" class="post-tag">#{{ $t->name }}</a>
                        @endforeach
                    </div>
                @endif
            </article>

            @if(session('success'))
                <div class="alert alert-success rounded-4 mt-3 mb-0">{{ session('success') }}</div>
            @endif

            {{-- Reactions --}}
            <div class="card-x react-card mt-4 reveal" id="reactions">
                <h5>Enjoyed this article?</h5>
                <small class="muted">Let {{ Str::before($author->name, ' ') }} know what you think.</small>

                <div class="react-row">
                    @foreach(\App\Models\Reaction::TYPES as $type => $emoji)
                        @php $active = $myReaction === $type; @endphp
                        @auth
                            @can('reactions.create')
                                <form method="POST" action="{{ route('posts.react', $post) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <button type="submit" class="btn react-btn {{ $active ? 'btn-dash' : 'btn-nav-outline' }}" title="{{ ucfirst($type) }}">
                                        <span class="emo">{{ $emoji }}</span>
                                        <span class="fw-semibold">{{ $reactionCounts[$type] ?? 0 }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="btn react-btn btn-nav-outline disabled" title="You don't have permission to react">
                                    <span class="emo">{{ $emoji }}</span>
                                    <span class="fw-semibold">{{ $reactionCounts[$type] ?? 0 }}</span>
                                </span>
                            @endcan
                        @else
                            <a href="{{ route('login') }}" class="btn react-btn btn-nav-outline" title="Login to react">
                                <span class="emo">{{ $emoji }}</span>
                                <span class="fw-semibold">{{ $reactionCounts[$type] ?? 0 }}</span>
                            </a>
                        @endauth
                    @endforeach
                </div>

                @guest
                    <small class="muted d-block mt-3"><a href="{{ route('login') }}">Login</a> to react to this post.</small>
                @endguest
            </div>

            {{-- Comments --}}
            <div class="card-x cm-card mt-4 reveal" id="comments">
                <div class="cm-head">
                    <h5><i class="bi bi-chat-dots"></i>Comments <span class="cm-count">{{ $comments->count() }}</span></h5>
                </div>

                @if($post->allow_comments)
                    @auth
                        @can('comments.create')
                            <form method="POST" action="{{ route('comments.store', $post) }}" class="cm-form">
                                @csrf
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="" referrerpolicy="no-referrer">
                                @else
                                    <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                                <div class="box">
                                    <textarea name="body" id="cmBody" rows="3" maxlength="1000" placeholder="Share your thoughts..."
                                              class="@error('body') is-invalid @enderror">{{ old('body') }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="foot">
                                        <small><span id="cmCount">0</span> / 1000</small>
                                        <button type="submit" class="btn btn-dash btn-sm px-4"><i class="bi bi-send me-1"></i>Post comment</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="cm-note"><i class="bi bi-lock"></i>You don't have permission to write comments.</div>
                        @endcan
                    @else
                        <div class="cm-note">
                            <i class="bi bi-person-circle"></i>
                            <span><a href="{{ route('login') }}" class="fw-semibold">Login</a> or <a href="{{ route('register') }}" class="fw-semibold">register</a> to join the conversation.</span>
                        </div>
                    @endauth
                @else
                    <div class="cm-note"><i class="bi bi-chat-square-x"></i>Comments are turned off for this post.</div>
                @endif

                @forelse($comments as $comment)
                    <div class="cm-item">
                        @if($comment->user->avatar)
                            <img src="{{ $comment->user->avatar }}" class="avatar" alt="" referrerpolicy="no-referrer">
                        @else
                            <span class="avatar">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                        @endif
                        <div class="cm-bubble">
                            <div class="cm-meta">
                                <strong>{{ $comment->user->name }}</strong>
                                @if($comment->user_id === $post->user_id)
                                    <span class="badge-cat">Author</span>
                                @endif
                                <small>{{ $comment->created_at->diffForHumans() }}</small>
                                @auth
                                    @if(auth()->id() === $comment->user_id || auth()->user()->can('comments.delete_any'))
                                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="ms-auto"
                                              onsubmit="return confirm('Delete this comment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cm-del" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                            <div class="cm-text">{!! nl2br(e($comment->body)) !!}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-cm">
                        <i class="bi bi-chat-heart"></i>
                        No comments yet.@if($post->allow_comments) Be the first to comment!@endif
                    </div>
                @endforelse
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="col-lg-4">
            <aside class="post-aside">

                <div class="card-x aside-card author-card reveal">
                    <div class="big-av">
                        @if($author->avatar)
                            <img src="{{ $author->avatar }}" alt="" referrerpolicy="no-referrer">
                        @else
                            {{ strtoupper(substr($author->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="muted small text-uppercase" style="letter-spacing:.1em;font-size:.68rem;font-weight:700">Written by</div>
                    <div class="nm">{{ $author->name }}</div>
                    @if($author->bio)
                        <p class="bio">{{ $author->bio }}</p>
                    @endif
                    <span class="cnt"><i class="bi bi-journal-richtext"></i>{{ $author->posts()->approved()->count() }} published {{ Str::plural('post', $author->posts()->approved()->count()) }}</span>
                    @if($author->website)
                        <div class="mt-2">
                            <a href="{{ $author->website }}" target="_blank" rel="noopener noreferrer" class="small fw-semibold text-decoration-none">
                                <i class="bi bi-link-45deg"></i>{{ parse_url($author->website, PHP_URL_HOST) ?: $author->website }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="card-x aside-card toc reveal" id="toc">
                    <h6 class="aside-title"><i class="bi bi-list-ul"></i>In this article</h6>
                    <ol id="tocList"></ol>
                </div>

                <div class="card-x aside-card reveal">
                    <h6 class="aside-title"><i class="bi bi-share"></i>Share this post</h6>
                    <div class="share-row">
                        <a class="share-btn wa" target="_blank" rel="noopener" title="WhatsApp" href="https://wa.me/?text={{ $ttl }}%20{{ $url }}"><i class="bi bi-whatsapp"></i></a>
                        <a class="share-btn fb" target="_blank" rel="noopener" title="Facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $url }}"><i class="bi bi-facebook"></i></a>
                        <a class="share-btn tw" target="_blank" rel="noopener" title="X" href="https://twitter.com/intent/tweet?text={{ $ttl }}&url={{ $url }}"><i class="bi bi-twitter-x"></i></a>
                        <button type="button" class="share-btn cp" id="copyLink" title="Copy link"><i class="bi bi-link-45deg"></i></button>
                    </div>
                </div>

            </aside>
        </div>
    </div>

    {{-- Related --}}
    @if($related->count())
        <div class="rel-head">
            <h3>More in {{ $post->category->name }}</h3>
            <a href="{{ route('categories.show', $post->category->slug) }}">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach($related as $r)
                <div class="col-sm-6 col-lg-4 reveal">
                    <a href="{{ route('posts.show', $r) }}" class="card-x post-card rel-card spot d-block text-decoration-none" style="color:inherit">
                        <div class="post-img" @if($r->image) style="background-image:url('{{ asset('storage/'.$r->image) }}')" @endif></div>
                        <div class="p-3">
                            <h6 class="mb-2">{{ $r->title }}</h6>
                            <small class="muted"><i class="bi bi-clock me-1"></i>{{ $r->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
(function () {
    // Table of contents from h2 / h3 inside the article
    const heads = [...document.querySelectorAll('#postBody h2, #postBody h3')];
    const toc = document.getElementById('toc'), list = document.getElementById('tocList');

    if (heads.length >= 2) {
        heads.forEach((h, i) => {
            if (!h.id) h.id = 'sec-' + (i + 1);
            const li = document.createElement('li');
            if (h.tagName === 'H3') li.className = 'sub';
            li.innerHTML = '<a href="#' + h.id + '"></a>';
            li.firstChild.textContent = h.textContent;
            list.appendChild(li);
        });
        toc.classList.add('show');

        const items = [...list.children];
        const spy = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (en.isIntersecting) {
                    const idx = heads.indexOf(en.target);
                    items.forEach((li, i) => li.classList.toggle('active', i === idx));
                }
            });
        }, { rootMargin: '-90px 0px -70% 0px' });
        heads.forEach(h => spy.observe(h));
    }

    // Copy link
    const cp = document.getElementById('copyLink');
    cp.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(location.href.split('#')[0]);
            cp.classList.add('done');
            cp.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => { cp.classList.remove('done'); cp.innerHTML = '<i class="bi bi-link-45deg"></i>'; }, 1800);
        } catch (e) { prompt('Copy this link:', location.href); }
    });

    // Comment counter
    const cm = document.getElementById('cmBody'), cc = document.getElementById('cmCount');
    if (cm) { const u = () => cc.textContent = cm.value.length; cm.addEventListener('input', u); u(); }
})();
</script>
@endsection