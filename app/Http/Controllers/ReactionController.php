<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $type = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(Reaction::TYPES))],
        ])['type'];

        $existing = Reaction::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && $existing->type === $type) {
            $existing->delete();                       // click again = remove reaction
        } elseif ($existing) {
            $existing->update(['type' => $type]);      // change reaction
        } else {
            Reaction::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'type'    => $type,
            ]);
        }

        return redirect(route('posts.show', $post) . '#reactions');
    }
}