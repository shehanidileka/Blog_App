<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        // ---- Default permissions ----
        $now = now();

        $permissions = [
            ['Posts',        'Create & edit own posts',  'posts.create',        'Can write new posts and edit or delete their own posts.'],
            ['Posts',        'Manage all posts',         'posts.manage',        'Can view, edit and delete any post (admin panel > All Posts).'],
            ['Comments',     'Write comments',           'comments.create',     'Can comment on posts.'],
            ['Comments',     'Delete any comment',       'comments.delete_any', 'Can delete comments written by other users.'],
            ['Reactions',    'React to posts',           'reactions.create',    'Can react to posts (like, love, etc.).'],
            ['Admin panel',  'Access admin panel',       'admin.access',        'Can open the admin panel overview.'],
            ['Admin panel',  'Manage categories',        'categories.manage',   'Can add, rename and delete categories.'],
            ['Admin panel',  'Manage users',             'users.manage',        'Can view users, change their roles and delete them.'],
            ['Admin panel',  'Manage roles',             'roles.manage',        'Can create roles and change role permissions.'],
            ['Admin panel',  'Manage mail settings',     'settings.manage',     'Can change the SMTP / mail settings.'],
        ];

        foreach ($permissions as [$group, $name, $slug, $description]) {
            DB::table('permissions')->insert([
                'group'       => $group,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $ids = DB::table('permissions')->pluck('id', 'slug');

        // ---- Default roles ----
        $adminId = DB::table('roles')->insertGetId([
            'name'        => 'Admin',
            'slug'        => 'admin',
            'description' => 'Full access to everything. This role cannot be changed.',
            'is_system'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $userId = DB::table('roles')->insertGetId([
            'name'        => 'User',
            'slug'        => 'user',
            'description' => 'Default role for registered users.',
            'is_system'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        foreach ($ids as $permissionId) {
            DB::table('permission_role')->insert(['permission_id' => $permissionId, 'role_id' => $adminId]);
        }

        foreach (['posts.create', 'comments.create', 'reactions.create'] as $slug) {
            DB::table('permission_role')->insert(['permission_id' => $ids[$slug], 'role_id' => $userId]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};