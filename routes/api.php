<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PostApiController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:10,1');

// Needs "Authorization: Bearer <token>"
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (\Illuminate\Http\Request $r) => $r->user());
    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/categories', [CategoryApiController::class, 'index']);

    Route::get('/posts', [PostApiController::class, 'index']);
    Route::get('/posts/{id}', [PostApiController::class, 'show']);
    Route::post('/posts', [PostApiController::class, 'store']);
    Route::match(['put', 'patch'], '/posts/{id}', [PostApiController::class, 'update']);
    Route::delete('/posts/{id}', [PostApiController::class, 'destroy']);
});