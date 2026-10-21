<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();          // saved copy, so the name survives if the user is deleted
            $table->string('action', 60)->index();            // e.g. post.created, user.login
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500);
            $table->json('properties')->nullable();          // what changed (old -> new)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });

        // New permission: who may open the Activity Log page
        $now = now();
        $id = DB::table('permissions')->insertGetId([
            'group'       => 'Admin panel',
            'name'        => 'View activity log',
            'slug'        => 'activity.view',
            'description' => 'Can open the activity log and see who did what.',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        if ($admin = DB::table('roles')->where('slug', 'admin')->value('id')) {
            DB::table('permission_role')->insert(['permission_id' => $id, 'role_id' => $admin]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        $id = DB::table('permissions')->where('slug', 'activity.view')->value('id');
        if ($id) {
            DB::table('permission_role')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};