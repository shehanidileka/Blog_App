<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('posts')
            ->with('roleModel')
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('id')->get();

        return view('admin.users', compact('users', 'roles'));
    }

    public function role(Request $request, User $user)
    {
        $data  = $request->validate(['role' => ['required', 'exists:roles,slug']]);
        $actor = $request->user();

        if ($user->id === $actor->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        if (! $actor->isAdmin() && ($user->isAdmin() || $data['role'] === 'admin')) {
            return back()->with('error', 'Only admins can change admin roles.');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', 'Role updated for ' . $user->name . '.');
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();

        if ($user->id === $actor->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && ! $actor->isAdmin()) {
            return back()->with('error', 'Only admins can delete an admin.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}