<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $featured   = Post::approved()->with(['category', 'user'])->withCount(['comments', 'reactions'])->latest()->first();
        $latest     = Post::approved()->with(['category', 'user'])->withCount(['comments', 'reactions'])->latest()->skip(1)->take(6)->get();
        $popular    = Post::approved()->orderByDesc('views')->take(4)->get();
        $categories = Category::withCount(['posts' => fn ($q) => $q->approved()])->get();

        $stats = [
            'posts'      => Post::approved()->count(),
            'writers'    => Post::approved()->distinct()->count('user_id'),
            'categories' => Category::count(),
            'views'      => (int) Post::approved()->sum('views'),
        ];

        return view('home', compact('featured', 'latest', 'popular', 'categories', 'stats'));
    }
}