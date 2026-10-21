<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $categories = Category::orderBy('name')
            ->withCount(['posts as my_posts_count' => fn ($q) => $q->where('user_id', $userId)])
            ->get();

        $posts = Post::with('category')
            ->where('user_id', $userId)
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $mine = Post::where('user_id', $userId);

        $stats = [
            'posts'      => (clone $mine)->count(),
            'views'      => (clone $mine)->sum('views'),
            'categories' => (clone $mine)->distinct()->count('category_id'),
        ];

        return view('dashboard.index', compact('posts', 'categories', 'stats'));
    }
}