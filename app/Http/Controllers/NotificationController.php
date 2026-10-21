<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $id)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        $data = $n->data;

        if (($data['status'] ?? '') === 'approved' && ! empty($data['slug'])
            && Post::where('slug', $data['slug'])->exists()) {
            return redirect()->route('posts.show', $data['slug']);
        }

        if (($data['status'] ?? '') === 'rejected' && ! empty($data['post_id'])
            && Post::whereKey($data['post_id'])->exists()) {
            return redirect()->route('dashboard.posts.edit', $data['post_id']);
        }

        return redirect()->route('dashboard');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}