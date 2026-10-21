<?php

use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMailSettingsController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyPostController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/category/{category:slug}', [PostController::class, 'category'])->name('categories.show');
Route::get('/tag/{tag:slug}', [PostController::class, 'tag'])->name('tags.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])
        ->middleware('can:comments.create')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/posts/{post:slug}/react', [ReactionController::class, 'toggle'])
        ->middleware('can:reactions.create')->name('posts.react');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
        // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showCode'])->name('password.code');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1')->name('password.update');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/posts/create', [MyPostController::class, 'create'])
        ->middleware('can:posts.create')->name('dashboard.posts.create');
    Route::post('/posts', [MyPostController::class, 'store'])
        ->middleware('can:posts.create')->name('dashboard.posts.store');
    Route::get('/posts/{post}/edit', [MyPostController::class, 'edit'])->name('dashboard.posts.edit');
    Route::put('/posts/{post}', [MyPostController::class, 'update'])->name('dashboard.posts.update');
    Route::delete('/posts/{post}', [MyPostController::class, 'destroy'])->name('dashboard.posts.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('can:posts.manage')->group(function () {
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::patch('/posts/{post:slug}/approve', [AdminPostController::class, 'approve'])->name('posts.approve');
        Route::patch('/posts/{post:slug}/reject', [AdminPostController::class, 'reject'])->name('posts.reject');
        Route::delete('/posts/{post:slug}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
    });

    Route::middleware('can:categories.manage')->group(function () {
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category:slug}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category:slug}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'role'])->name('users.role');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('can:roles.manage')->group(function () {
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role:slug}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role:slug}', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role:slug}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('permissions.store');
        Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    Route::middleware('can:activity.view')->group(function () {
        Route::get('/activity', [AdminActivityController::class, 'index'])->name('activity.index');
        Route::delete('/activity', [AdminActivityController::class, 'prune'])->name('activity.prune');
    });

    Route::middleware('can:settings.manage')->group(function () {
        Route::get('/settings/mail', [AdminMailSettingsController::class, 'edit'])->name('settings.mail');
        Route::put('/settings/mail', [AdminMailSettingsController::class, 'update'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [AdminMailSettingsController::class, 'test'])->name('settings.mail.test');
    });
});