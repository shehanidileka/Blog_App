<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'      => User::count(),
            'posts'      => Post::approved()->count(),
            'pending'    => Post::pending()->count(),
            'categories' => Category::count(),
            'views'      => Post::sum('views'),
        ];

        $byCategory  = Category::withCount('posts')->orderByDesc('posts_count')->get();
        $max         = max(1, $byCategory->max('posts_count'));
        $latestPosts  = Post::where('status', '!=', 'draft')->with(['user', 'category'])->latest()->take(6)->get();
        $latestUsers = User::latest()->take(5)->get();

        return view('admin.index', compact('stats', 'byCategory', 'max', 'latestPosts', 'latestUsers'));
    }
}