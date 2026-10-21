<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPermissionController extends Controller
{
    /**
     * Permissions the application code checks directly (can:posts.manage, @can('users.manage') ...).
     * Their slug must never change and they cannot be deleted, otherwise the site breaks.
     */
    public const BUILT_IN = [
        'posts.create', 'posts.manage',
        'comments.create', 'comments.delete_any',
        'reactions.create',
        'admin.access', 'categories.manage', 'users.manage', 'roles.manage', 'settings.manage', 'activity.view',
    ];

    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('group')->orderBy('id')->get();

        return view('admin.permissions', [
            'groups'   => $permissions->groupBy('group'),
            'allGroups' => $permissions->pluck('group')->unique()->values(),
            'builtIn'  => self::BUILT_IN,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'slug'        => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/', 'unique:permissions,slug'],
            'group'       => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'slug.regex' => 'Use lowercase letters, numbers and underscores in the form group.action (e.g. posts.publish).',
        ]);

        $permission = Permission::create($data);

        // The Admin role always has every permission.
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $adminRole?->permissions()->syncWithoutDetaching([$permission->id]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created. Assign it to a role under Roles & Permissions.');
    }

    public function update(Request $request, Permission $permission)
    {
        $builtIn = in_array($permission->slug, self::BUILT_IN, true);

        $rules = [
            'name'        => ['required', 'string', 'max:80'],
            'group'       => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ];

        if (! $builtIn) {
            $rules['slug'] = ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/', Rule::unique('permissions', 'slug')->ignore($permission->id)];
        }

        $permission->update($request->validate($rules));

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        if (in_array($permission->slug, self::BUILT_IN, true)) {
            return back()->with('error', 'Built-in permissions are used by the application and cannot be deleted.');
        }

        $permission->roles()->detach();
        $permission->delete();

        return back()->with('success', 'Permission deleted.');
    }
}