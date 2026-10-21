<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function index(Request $request)
    {
        return $this->listing($request, null, null);
    }

    public function category(Request $request, Category $category)
    {
        return $this->listing($request, $category, null);
    }

    public function tag(Request $request, Tag $tag)
    {
        return $this->listing($request, null, $tag);
    }

    public function show(Request $request, Post $post)
    {
        if (! $post->isApproved()) {
            $user = $request->user();

            abort_unless($user && ($user->id === $post->user_id || $user->can('posts.manage')), 404);
        }

        // Count a view only for approved posts, once per visitor session
        if ($post->isApproved()) {
            $viewed = $request->session()->get('viewed_posts', []);
            if (! in_array($post->id, $viewed)) {
                $post->increment('views');
                $request->session()->push('viewed_posts', $post->id);
            }
        }

        $post->load(['category', 'user', 'tags']);

        $related = Post::approved()->with('category')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();

        $comments = $post->comments()->with('user')->latest()->get();

        $reactionCounts = $post->reactions()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $myReaction = $request->user()
            ? $post->reactions()->where('user_id', $request->user()->id)->value('type')
            : null;

        return view('posts.show', compact('post', 'related', 'comments', 'reactionCounts', 'myReaction'));
    }

    private function listing(Request $request, ?Category $category, ?Tag $tag)
    {
        $q = trim((string) $request->query('q', ''));

        $posts = Post::approved()->with(['category', 'user'])
            ->withCount(['comments', 'reactions'])
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($tag, fn ($query) => $query->whereHas('tags', fn ($t) => $t->where('tags.id', $tag->id)))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('excerpt', 'like', "%{$q}%")
                      ->orWhere('body', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = Category::withCount(['posts' => fn ($query) => $query->approved()])->orderBy('name')->get();

        return view('posts.index', compact('posts', 'categories', 'category', 'tag', 'q'));
    }
}