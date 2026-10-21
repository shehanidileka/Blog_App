@php
    $seo     = \App\Support\Seo::class;
    $catName = optional($post->category)->name;
    $isNew   = ! $post->exists;

    // A field is "auto" when it is empty or still equals the automatic suggestion
    $auto = [
        'meta_title'       => $isNew || ! $post->meta_title       || $post->meta_title       === $seo::title($post->title),
        'meta_description' => $isNew || ! $post->meta_description || $post->meta_description === $seo::description($post->excerpt, $post->body),
        'meta_keywords'    => $isNew || ! $post->meta_keywords    || $post->meta_keywords    === $seo::keywords($post->title, $post->body, $catName),
        'image_alt'        => $isNew || ! $post->image_alt        || $post->image_alt        === $post->title,
    ];

    // after a validation error keep exactly what the user typed
    if (session()->hasOldInput()) {
        foreach ($auto as $k => $v) { $auto[$k] = blank(old($k)); }
    }
@endphp


<div class="col-12 seo-section" id="seoSection" data-slug="{{ $post->slug }}" data-host="{{ parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost() }}">

    <div class="seo-head">
        <div>
            <h6 class="mb-1"><i class="bi bi-stars me-1" style="color:var(--accent)"></i>SEO &amp; Meta details</h6>
            <p class="muted small mb-0">Suggested automatically from your title, description, content, category and tags. Edit anything you like.</p>
        </div>
        <button type="button" class="btn btn-nav-outline btn-sm" id="seoRegen"><i class="bi bi-magic me-1"></i>Re-generate all</button>
    </div>

    <div class="row g-3 mt-1">

        {{-- Meta title --}}
        <div class="col-12 seo-field" data-field="meta_title" data-auto="{{ $auto['meta_title'] ? 1 : 0 }}" data-min="30" data-max="60">
            <div class="seo-label">
                <label class="form-label mb-0" for="meta_title">Meta title</label>
                <span class="seo-state"></span>
                <button type="button" class="seo-reset">Use suggestion</button>
                <span class="counter ms-auto" data-count></span>
            </div>
            <input type="text" name="meta_title" id="meta_title" maxlength="70" class="form-control"
                   value="{{ old('meta_title', $post->meta_title) }}" placeholder="Suggested from your title">
            <div class="seo-hint">The blue headline people see on Google. Best between 30 and 60 characters.</div>
        </div>

        {{-- Meta description --}}
        <div class="col-12 seo-field" data-field="meta_description" data-auto="{{ $auto['meta_description'] ? 1 : 0 }}" data-min="120" data-max="160">
            <div class="seo-label">
                <label class="form-label mb-0" for="meta_description">Meta description</label>
                <span class="seo-state"></span>
                <button type="button" class="seo-reset">Use suggestion</button>
                <span class="counter ms-auto" data-count></span>
            </div>
            <textarea name="meta_description" id="meta_description" rows="3" maxlength="170" class="form-control"
                      placeholder="Suggested from your short description or content">{{ old('meta_description', $post->meta_description) }}</textarea>
            <div class="seo-hint">The short text under the headline. Best between 120 and 160 characters.</div>
        </div>

        {{-- Keywords --}}
        <div class="col-md-7 seo-field" data-field="meta_keywords" data-auto="{{ $auto['meta_keywords'] ? 1 : 0 }}" data-min="3" data-max="10" data-unit="keywords">
            <div class="seo-label">
                <label class="form-label mb-0" for="meta_keywords">Keywords</label>
                <span class="seo-state"></span>
                <button type="button" class="seo-reset">Use suggestion</button>
                <span class="counter ms-auto" data-count></span>
            </div>
            <input type="text" name="meta_keywords" id="meta_keywords" maxlength="255" class="form-control"
                   value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="category, tags and the most used words">
            <div class="seo-hint">Separate with commas. 3 to 10 keywords is a good number.</div>
        </div>

        {{-- Image alt --}}
        <div class="col-md-5 seo-field" data-field="image_alt" data-auto="{{ $auto['image_alt'] ? 1 : 0 }}" data-min="5" data-max="125">
            <div class="seo-label">
                <label class="form-label mb-0" for="image_alt">Cover image alt text</label>
                <span class="seo-state"></span>
                <button type="button" class="seo-reset">Use suggestion</button>
                <span class="counter ms-auto" data-count></span>
            </div>
            <input type="text" name="image_alt" id="image_alt" maxlength="125" class="form-control"
                   value="{{ old('image_alt', $post->image_alt) }}" placeholder="Describe the cover image">
            <div class="seo-hint">Helps screen readers and image search.</div>
        </div>

        {{-- Previews --}}
        <div class="col-12">
            <div class="seo-prev-grid">
                <div>
                    <div class="seo-prev-title"><i class="bi bi-google me-1"></i>Google preview</div>
                    <div class="gp">
                        <div class="gp-site">
                            <span class="gp-fav">M</span>
                            <div style="min-width:0">
                                <div class="gp-name">MyBlog</div>
                                <div class="gp-url" id="gpUrl"></div>
                            </div>
                        </div>
                        <div class="gp-title" id="gpTitle"></div>
                        <div class="gp-desc" id="gpDesc"></div>
                    </div>
                </div>
                <div>
                    <div class="seo-prev-title"><i class="bi bi-share me-1"></i>Social share preview</div>
                    <div class="sp">
                        <div class="sp-img" id="spImg"><i class="bi bi-image"></i></div>
                        <div class="sp-body">
                            <small id="spHost"></small>
                            <b id="spTitle"></b>
                            <span id="spDesc"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Score --}}
        <div class="col-12">
            <div class="card-x p-3" style="box-shadow:none">
                <div class="seo-score">
                    <span class="fw-semibold small"><i class="bi bi-graph-up-arrow me-1"></i>SEO checklist</span>
                    <b id="seoScore">0 / 4</b>
                </div>
                <div class="seo-meter"><span id="seoBar"></span></div>
                <ul class="seo-checks" id="seoChecks"></ul>
            </div>
        </div>
    </div>
</div>