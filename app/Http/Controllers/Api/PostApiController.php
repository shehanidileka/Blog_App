<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostApiController extends Controller
{
    // GET /api/posts?category_id=&q=
    public function index(Request $request)
    {
        $posts = Post::with(['category:id,name', 'user:id,name'])
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $posts]);
    }

    // GET /api/posts/{id}
    public function show(int $id)
    {
        $post = Post::with(['category:id,name', 'user:id,name'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $post]);
    }

    // POST /api/posts
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt'     => ['nullable', 'string'],
            'body'        => ['required', 'string'],
        ]);

        $data['user_id'] = $request->user()->id;
        $post = Post::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Post created.',
            'data'    => $post->load('category:id,name'),
        ], 201);
    }

    // PUT/PATCH /api/posts/{id}
    public function update(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $this->authorizeOwner($request, $post);

        $data = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'slug'        => ['sometimes', 'required', 'string', 'max:255', Rule::unique('posts')->ignore($post->id)],
            'excerpt'     => ['nullable', 'string'],
            'body'        => ['sometimes', 'required', 'string'],
        ]);

        $post->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Post updated.',
            'data'    => $post->load('category:id,name'),
        ]);
    }

    // DELETE /api/posts/{id}
    public function destroy(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $this->authorizeOwner($request, $post);
        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted.']);
    }

    // Only the author or an admin may change a post.
    private function authorizeOwner(Request $request, Post $post): void
    {
        $user = $request->user();
        abort_unless($user->id === $post->user_id || $user->role === 'admin', 403, 'Not allowed.');
    }
}