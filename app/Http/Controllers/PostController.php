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
                    'user' => $post->user,
                    'likes_count' => $post->likes->count(),
                    'liked' => $post->likes->contains('user_id', auth()->id()),
                    'comments' => $post->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'content' => $comment->content,
                            'user' => $comment->user,
                        ];
                    }),
                ];
            });


        return Inertia::render('Feed', [
            'posts' => $posts,
            'authUser' => auth()->user()
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
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

        return back(); // or redirect()->route('feed')
    }
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $post->update([
            'content' => $request->content,
        ]);
        return back(); // or redirect with Inertia response if needed
    }
    public function destroy(Post $post)
    {
        $post->delete();
        return back(); // or Inertia redirect
    }
}
