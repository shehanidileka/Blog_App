@extends('layouts.app')

@section('title', ($tag ? '#' . $tag->name : ($category ? $category->name : 'All Posts')) . ' · MyBlog')

@section('content')
<h4 class="section-title mb-1">
    @if($q !== '')
        Search results for "{{ $q }}"
    @elseif($tag)
        #{{ $tag->name }}
    @else
        {{ $category ? $category->name : 'All Posts' }}
    @endif
</h4>
<p class="muted small mb-3">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} found</p>

{{-- Category filter --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('posts.index', $q !== '' ? ['q' => $q] : []) }}"
       class="btn btn-sm rounded-pill {{ ! $category && ! $tag ? 'btn-dash' : 'btn-nav-outline' }}">All</a>
    @foreach($categories as $c)
        <a href="{{ route('categories.show', array_filter(['category' => $c->slug, 'q' => $q])) }}"
           class="btn btn-sm rounded-pill {{ $category && $category->id === $c->id ? 'btn-dash' : 'btn-nav-outline' }}">
            {{ $c->name }} <span class="opacity-75">({{ $c->posts_count }})</span>
        </a>
    @endforeach
    @if($tag)
        <a href="{{ route('posts.index') }}" class="btn btn-sm rounded-pill btn-dash">
            #{{ $tag->name }} <i class="bi bi-x-lg ms-1"></i>
        </a>
    @endif
</div>

<div class="row g-3">
    @forelse($posts as $post)
        <div class="col-sm-6 col-lg-4">
            <a href="{{ route('posts.show', $post) }}" class="card-x post-card d-block text-decoration-none" style="color:inherit">
                <div class="post-img" @if($post->image) style="background-image:url('{{ asset('storage/'.$post->image) }}')" @endif></div>
                <div class="p-3">
                    <span class="badge-cat">{{ $post->category->name }}</span>
                    <h6 class="mt-2 mb-1">{{ $post->title }}</h6>
                    <p class="muted small mb-2">{{ Str::limit($post->excerpt, 80) }}</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="muted"><i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}</small>
                        <small class="muted d-flex gap-3">
                            <span title="Reactions"><i class="bi bi-heart me-1"></i>{{ $post->reactions_count }}</span>
                            <span title="Comments"><i class="bi bi-chat-dots me-1"></i>{{ $post->comments_count }}</span>
                        </small>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <p class="muted">No posts found.</p>
            <a href="{{ route('home') }}" class="btn btn-dash btn-sm">Back to home</a>
        </div>
    @endforelse
</div>

@if($posts->hasPages())
    <div class="mt-4 d-flex justify-content-center">{{ $posts->links() }}</div>
@endif
@endsection