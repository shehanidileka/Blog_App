@extends('layouts.panel')

@section('title', ($post->exists ? 'Edit Post' : 'New Post') . ' · MyBlog')

@section('panel')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

@php
    $isOwner       = ! $post->exists || $post->user_id === auth()->id();
    $initialBody   = \App\Support\Html::render(old('body', $post->body));
    $tagsValue     = old('tags', $post->exists ? $post->tags->pluck('name')->implode(',') : '');
    $allowComments = (bool) old('allow_comments', $post->exists ? $post->allow_comments : true);
@endphp

<div class="card-x p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $post->exists ? 'Edit Post' : 'Create New Post' }}</h4>
            @if($post->exists)
                @php $b = ['draft' => 'secondary', 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$post->status] ?? 'secondary'; @endphp
                <span class="badge text-bg-{{ $b }}">{{ ucfirst($post->status) }}</span>
            @endif
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-nav-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    @if($post->exists && $post->status === 'rejected' && $post->rejection_reason)
        <div class="alert alert-danger py-2 small">
            <strong>Rejected:</strong> {{ $post->rejection_reason }}
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" id="postForm"
          action="{{ $post->exists ? route('dashboard.posts.update', $post) : route('dashboard.posts.store') }}">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-8">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="title">Title</label>
                    <span class="counter" id="titleCount"></span>
                </div>
                <input type="text" name="title" id="title" maxlength="255" value="{{ old('title', $post->title) }}" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $post->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="excerpt">Short description</label>
                    <span class="counter" id="excerptCount"></span>
                </div>
                <textarea name="excerpt" id="excerpt" rows="2" maxlength="300" class="form-control">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Content</label>
                <div class="editor-wrap"><div id="editor">{!! $initialBody !!}</div></div>
                <input type="hidden" name="body" id="bodyInput">
                <div class="counter mt-2" id="wordCount"></div>
            </div>

            <div class="col-12 tags-field">
                <label class="form-label">Tags <span class="muted fw-normal">(up to 5, press Enter or comma)</span></label>
                <div class="tag-box" id="tagBox">
                    <input type="text" id="tagInput" placeholder="e.g. laravel, php, tips" maxlength="30" autocomplete="off">
                </div>
                <input type="hidden" name="tags" id="tagsHidden" value="{{ $tagsValue }}">
            </div>

            <div class="col-12">
                <label class="form-label">Cover image</label>
                <input type="file" name="image" id="image" accept="image/*" class="form-control">
                <div class="counter mt-1">JPG, PNG or WebP, max 2 MB.</div>
                <img id="preview" class="mt-3 rounded-3 {{ $post->image ? '' : 'd-none' }}" style="max-height:220px; max-width:100%"
                     src="{{ $post->image_url }}" alt="">
            </div>

            <div class="col-12">
                <input type="hidden" name="allow_comments" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="allow_comments" id="allow_comments" value="1" @checked($allowComments)>
                    <label class="form-check-label" for="allow_comments">Allow comments on this post</label>
                </div>
            </div>

            {{-- NEW: SEO & Meta details (suggested from the fields above) --}}
            @include('partials.meta-fields')
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
            @if($isOwner)
                <button type="submit" name="action" value="draft" class="btn btn-nav-outline px-4">
                    <i class="bi bi-save me-1"></i>Save as draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-dash px-4">
                    @can('posts.manage')
                        <i class="bi bi-send me-1"></i>{{ $post->exists ? 'Update & publish' : 'Publish' }}
                    @else
                        <i class="bi bi-send me-1"></i>Submit for review
                    @endcan
                </button>
            @else
                <button type="submit" name="action" value="submit" class="btn btn-dash px-4">Update post</button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-nav-outline">Cancel</a>
        </div>

        @cannot('posts.manage')
            @if($isOwner)
                <p class="small muted mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    A draft is only visible to you. Submitted posts go live after an admin approves them.
                </p>
            @endif
        @endcannot
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    // ---------- Rich text editor ----------
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Write your post...',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    const wordCount = document.getElementById('wordCount');
    function updateWords() {
        const text = quill.getText().trim();
        const words = text ? text.split(/\s+/).length : 0;
        wordCount.textContent = words + ' words · ' + Math.max(1, Math.ceil(words / 200)) + ' min read';
    }
    quill.on('text-change', updateWords);
    updateWords();

    // ---------- Character counters ----------
    function counter(inputId, countId, max) {
        const input = document.getElementById(inputId);
        const out = document.getElementById(countId);
        const update = () => { out.textContent = input.value.length + ' / ' + max; };
        input.addEventListener('input', update);
        update();
    }
    counter('title', 'titleCount', 255);
    counter('excerpt', 'excerptCount', 300);

    // ---------- Tags ----------
    const tagBox = document.getElementById('tagBox');
    const tagInput = document.getElementById('tagInput');
    const tagsHidden = document.getElementById('tagsHidden');
    let tags = tagsHidden.value ? tagsHidden.value.split(',').map(s => s.trim()).filter(Boolean) : [];

    function renderTags() {
        tagBox.querySelectorAll('.tag-chip').forEach(el => el.remove());
        tags.forEach((t, i) => {
            const chip = document.createElement('span');
            chip.className = 'tag-chip';
            chip.appendChild(document.createTextNode('#' + t));
            const x = document.createElement('button');
            x.type = 'button';
            x.setAttribute('aria-label', 'Remove tag');
            x.innerHTML = '&times;';
            x.addEventListener('click', () => { tags.splice(i, 1); renderTags(); });
            chip.appendChild(x);
            tagBox.insertBefore(chip, tagInput);
        });
        tagsHidden.value = tags.join(',');
        tagInput.placeholder = tags.length >= 5 ? 'Maximum 5 tags' : 'e.g. laravel, php, tips';
        tagInput.disabled = tags.length >= 5;
    }

    function addTag(raw) {
        const v = raw.replace(/,/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 30);
        if (!v || tags.length >= 5 || tags.some(t => t.toLowerCase() === v.toLowerCase())) return;
        tags.push(v);
        renderTags();
    }

    tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(tagInput.value);
            tagInput.value = '';
        } else if (e.key === 'Backspace' && !tagInput.value && tags.length) {
            tags.pop();
            renderTags();
        }
    });
    tagInput.addEventListener('blur', () => { addTag(tagInput.value); tagInput.value = ''; });
    tagBox.addEventListener('click', () => tagInput.focus());
    renderTags();

    // ---------- Image preview ----------
    document.getElementById('image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const img = document.getElementById('preview');
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
    });

    // ---------- Submit ----------
    document.getElementById('postForm').addEventListener('submit', () => {
        addTag(tagInput.value);
        tagInput.value = '';
        document.getElementById('bodyInput').value = quill.getText().trim().length ? quill.root.innerHTML : '';
    });
</script>

{{-- NEW: live suggestions for the SEO & Meta section --}}
@include('partials.meta-script')
@endsection