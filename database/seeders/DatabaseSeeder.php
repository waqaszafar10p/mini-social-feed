<?php

namespace Database\Seeders;

use App\Models\Comment as ModelsComment;
use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {

        $predefinedUsers = collect([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
            ['name' => 'David', 'email' => 'david@example.com'],
            ['name' => 'Eve', 'email' => 'eve@example.com'],
        ]);

        $users = $predefinedUsers->map(function ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('password'), // ✅ use same password for all
            ]);
        });

        $users->each(function ($user) use ($users) {
            $posts = Post::factory()
                ->count(5)
                ->for($user)
                ->create();

            $posts->each(function ($post) use ($users) {
                // Comments
                ModelsComment::factory()
                    ->count(rand(2, 4))
                    ->make()
                    ->each(function ($comment) use ($post, $users) {
                        $comment->post_id = $post->id;
                        $comment->user_id = $users->random()->id;
                        $comment->save();
                    });

                // Likes
                $post->likes()->createMany(
                    $users->random(rand(1, 3))->map(fn($user) => [
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray()
                );
            });
        });
    }
}
