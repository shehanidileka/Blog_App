<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MyPostController extends Controller
{
    public function create()
    {
        return view('dashboard.form', [
            'post'       => new Post,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug']    = $this->uniqueSlug($data['title']);

        if ($this->isDraft($request)) {
            $data['status']      = 'draft';
            $data['approved_at'] = null;
            $data['approved_by'] = null;
            $message = 'Draft saved. Submit it for review when you are ready.';
        } elseif ($request->user()->can('posts.manage')) {
          
            $data['status']      = 'approved';
            $data['approved_at'] = now();
            $data['approved_by'] = $request->user()->id;
            $message = 'Post published successfully.';
        } else {
            $data['status']      = 'pending';
            $data['approved_at'] = null;
            $data['approved_by'] = null;
            $message = 'Post submitted! It will go live once an admin approves it. We will notify you.';
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create($data);
        $this->syncTags($post, $request->input('tags'));

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function edit(Post $post)
    {
        $this->authorizePost($post);

        return view('dashboard.form', [
            'post'       => $post,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizePost($post);

        $user    = $request->user();
        $isOwner = $post->user_id === $user->id;
        $data    = $this->validated($request);

        if (! $isOwner) {
           
            $message = 'Post updated successfully.';
        } elseif ($this->isDraft($request)) {
            $data['status']           = 'draft';
            $data['approved_at']      = null;
            $data['approved_by']      = null;
            $data['rejection_reason'] = null;
            $message = 'Draft saved.';
        } elseif ($user->can('posts.manage')) {
            $data['status']           = 'approved';
            $data['rejection_reason'] = null;
            if (! $post->isApproved()) {
                $data['approved_at'] = now();
                $data['approved_by'] = $user->id;
            }
            $message = 'Post updated successfully.';
        } else {
            // a normal user's edit goes back to review
            $data['status']           = 'pending';
            $data['approved_at']      = null;
            $data['approved_by']      = null;
            $data['rejection_reason'] = null;
            $message = 'Post sent for approval. We will notify you.';
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);
        $this->syncTags($post, $request->input('tags'));

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function destroy(Post $post)
    {
        $this->authorizePost($post);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    private function isDraft(Request $request): bool
    {
        return $request->input('action') === 'draft';
    }

    private function authorizePost(Post $post): void
    {
        $user = auth()->user();

        abort_unless(
            ($post->user_id === $user->id && $user->can('posts.create')) || $user->can('posts.manage'),
            403
        );
    }

    private function validated(Request $request): array
    {
        $isDraft = $this->isDraft($request);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'excerpt'     => ['nullable', 'string', 'max:300'],
            'body'        => [$isDraft ? 'nullable' : 'required', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'tags'        => ['nullable', 'string', 'max:300'],
            'action'      => ['nullable', 'in:draft,submit'],

            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
            'meta_keywords'    => ['nullable', 'string', 'max:255'],
            'image_alt'        => ['nullable', 'string', 'max:125'],
        ]);

        $body = Html::clean($validated['body'] ?? '');

        if (! $isDraft && trim(strip_tags($body)) === '') {
            throw ValidationException::withMessages(['body' => 'The content field is required.']);
        }

        return [
            'title'          => $validated['title'],
            'category_id'    => $validated['category_id'],
            'excerpt'        => $validated['excerpt'] ?? null,
            'body'           => trim(strip_tags($body)) === '' ? '' : $body,
            'allow_comments' => $request->boolean('allow_comments'),

            'meta_title'       => $this->oneLine($validated['meta_title'] ?? null),
            'meta_description' => $this->oneLine($validated['meta_description'] ?? null),
            'meta_keywords'    => $this->oneLine($validated['meta_keywords'] ?? null),
            'image_alt'        => $this->oneLine($validated['image_alt'] ?? null),
        ];
    }

    private function syncTags(Post $post, ?string $csv): void
    {
        $names = collect(explode(',', (string) $csv))
            ->map(fn ($t) => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($t))), 30, ''))
            ->filter()
            ->unique(fn ($t) => mb_strtolower($t))
            ->take(5);

        $ids = $names->map(function ($name) {
            $slug = Str::slug($name) ?: 't-' . substr(md5(mb_strtolower($name)), 0, 10);

            return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        })->all();

        $post->tags()->sync($ids);
    }

    private function oneLine(?string $value): ?string
    {
        $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));

        return $value === '' ? null : $value;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}