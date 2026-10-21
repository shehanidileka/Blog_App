<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('id')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', [
            'role'     => new Role,
            'groups'   => $this->groups(),
            'selected' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::create([
            'name'        => $data['name'],
            'slug'        => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', [
            'role'     => $role,
            'groups'   => $this->groups(),
            'selected' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            return redirect()->route('admin.roles.index')->with('error', 'The Admin role cannot be changed.');
        }

        $data = $this->validated($request, $role);

        $role->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $changed = $role->permissions()->sync($data['permissions'] ?? []);

        if ($changed['attached'] || $changed['detached']) {
            $names = fn ($ids) => Permission::whereIn('id', $ids)->pluck('slug')->all();
            Activity::log('role.permissions_updated', 'Permissions of role changed: ' . $role->name, $role, [
                'added'   => ['old' => null, 'new' => implode(', ', $names($changed['attached'])) ?: null],
                'removed' => ['old' => implode(', ', $names($changed['detached'])) ?: null, 'new' => null],
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'This role is assigned to users. Move them to another role first.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function groups()
    {
        return Permission::orderBy('id')->get()->groupBy('group');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;

        while (Role::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}