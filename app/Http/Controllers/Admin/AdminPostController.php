<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPostController extends Controller
{
    public function index(Request $request)
    {
        // drafts are private to their authors
        $posts = Post::with(['user', 'category'])
            ->where('status', '!=', 'draft')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->author, fn ($q, $a) => $q->where('user_id', $a))
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $authors    = User::orderBy('name')->get(['id', 'name']);
        $counts     = Post::where('status', '!=', 'draft')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.posts', compact('posts', 'categories', 'authors', 'counts'));
    }

    public function approve(Post $post)
    {
        if ($post->status === 'draft') {
            return back()->with('error', 'This post is still a draft. The author has not submitted it yet.');
        }

        if ($post->isApproved()) {
            return back()->with('success', 'This post is already approved.');
        }

        $post->update([
            'status'           => 'approved',
            'approved_at'      => now(),
            'approved_by'      => auth()->id(),
            'rejection_reason' => null,
        ]);

        $sent = $this->notifyAuthor($post, true);

        return back()->with('success', $sent
            ? 'Post approved. The author has been notified.'
            : 'Post approved. (The in-app notification was saved, but the email could not be sent.)');
    }

    public function reject(Request $request, Post $post)
    {
        if ($post->status === 'draft') {
            return back()->with('error', 'This post is still a draft. The author has not submitted it yet.');
        }

        $data   = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $reason = trim($data['reason'] ?? '') ?: null;

        $post->update([
            'status'           => 'rejected',
            'approved_at'      => null,
            'approved_by'      => null,
            'rejection_reason' => $reason,
        ]);

        $sent = $this->notifyAuthor($post, false, $reason);

        return back()->with('success', $sent
            ? 'Post rejected. The author has been notified.'
            : 'Post rejected. (The in-app notification was saved, but the email could not be sent.)');
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    private function notifyAuthor(Post $post, bool $approved, ?string $reason = null): bool
    {
        if ($post->user_id === auth()->id()) {
            return true;
        }

        try {
            $post->user->notify(new PostReviewed($post, $approved, $reason));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}