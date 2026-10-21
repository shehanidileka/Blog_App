<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->allow_comments, 403, 'Comments are turned off for this post.');

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        return redirect(route('posts.show', $post) . '#comments')
            ->with('success', 'Comment added.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        abort_unless($comment->user_id === $user->id || $user->can('comments.delete_any'), 403);

        $post = $comment->post;
        $comment->delete();

        return redirect(route('posts.show', $post) . '#comments')
            ->with('success', 'Comment deleted.');
    }
}