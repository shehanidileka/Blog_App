<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryApiController extends Controller
{
    // GET /api/categories
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => Category::select('id', 'name')->orderBy('name')->get(),
        ]);
    }
}