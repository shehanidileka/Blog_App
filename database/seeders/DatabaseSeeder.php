<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
    'name'  => 'Admin',
    'email' => 'admin@test.com',
    'role'  => 'admin',
]);

        $names = [
            'Food & cooking', 'education', 'News', 'Technology', 'Travel',
            'Health & Fitness', 'Lifestyle', 'Sports', 'Entertainment', 'Business',
        ];

        $cats = [];
        foreach ($names as $name) {
            $cats[$name] = Category::create(['name' => $name, 'slug' => Str::slug($name)]);
        }

        Post::create([
            'category_id' => $cats['Food & cooking']->id,
            'user_id'     => $user->id,
            'title'       => 'Fresh & Healthy Tortilla Wraps with Meat and Vegetables',
            'slug'        => 'tortilla-wraps',
            'excerpt'     => 'Crispy tortilla wraps loaded with juicy meat, fresh lettuce, cucumber and tomatoes.',
            'body'        => 'Lorem ipsum dolor sit amet...',
            'views'       => 1,
        ]);

        Post::create([
            'category_id' => $cats['education']->id,
            'user_id'     => $user->id,
            'title'       => 'HTML',
            'slug'        => 'html',
            'body'        => 'HTML basics...',
            'views'       => 4,
        ]);
    }
}