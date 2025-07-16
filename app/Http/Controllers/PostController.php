<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'user:id,name',
            'likes',
            'comments.user:id,name'
        ])
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'created_at' => $post->created_at,
                    'user' => $post->user,
                    'likes_count' => $post->likes->count(),
                    'liked' => $post->likes->contains('user_id', auth()->id()),
                    'comments' => $post->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'content' => $comment->content,
                            'user' => $comment->user,
                            'created_at' => $comment->created_at,
                        ];
                    }),
                ];
            });


        return Inertia::render('Feed', [
            'posts' => $posts
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:50|max:1000',
        ]);

        auth()->user()->posts()->create($validated);

        return redirect()->route('feed')->with('success', 'Post created!');
    }
    public function toggleLike(Post $post)
    {
        $user = auth()->user();

        $alreadyLiked = $post->likes()->where('user_id', $user->id)->exists();

        if ($alreadyLiked) {
            // Unlike
            $post->likes()->where('user_id', $user->id)->delete();
        } else {
            // Like
            $post->likes()->create(['user_id' => $user->id]);
        }

        return back();
    }
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string||min:50|max:1000',
        ]);

        $post->update([
            'content' => $request->content,
        ]);
        return back();
    }
    public function destroy(Post $post)
    {
        $post->delete();
        return back();
    }
}
