<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $post = Post::with('user.comments')->get();

        $posts = $post->map(function ($post) {
            return [
                "post_id" => $post->id,
                "title" => $post->title,
                "content" => $post->content,
                "author" => $post->user->username,
                "created_at" => $post->created_at,
                "comments" => $post->comments->map(function ($comment) {
                    return [
                        "comment_id" => $comment->id,
                        "commentor" => $comment->user->username,
                        "comment" => $comment->content
                    ];
                })
            ];
        });

        return response()->json(["Feed" => $posts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => ['required'],
            'content' => ['required'],
        ]);

        $attributes = [
            "user_id" => auth()->id(),
            'title' => $request->title,
            'content' => $request->content
        ];

        $post = Post::create($attributes);
        $post = [
            'title' => $post->title,
            'content' => $post->content
        ];

        return response()->json([
            'message' => 'Post created successfully',
            'post'    => $post
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::with(['user.comments'])->find($id);

        if (!$post) {
            return response()->json(["message" => "post not found"]);
        }

        $posts = [
            "post_id" => $post->id,
            "title" => $post->title,
            "content" => $post->content,
            "author" => $post->user->username,
            "created_at" => $post->created_at->toDateTimeString(),
            "comments" => $post->comments->map(function ($comment) {
                return [
                    "comment_id" => $comment->id,
                    "commentor" => $comment->user->username,
                    "comment" => $comment->content
                ];
            })
        ];

        return response()->json($posts);
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [];
        if ($request->has('title')) {
            $rules['title'] = 'required|string';
        }
        if ($request->has('content')) {
            $rules['content'] = 'required|string';
        }

        if (empty($rules)) {
            return response()->json(['message' => 'At least one of title or content is required']);
        }

        $validated = $request->validate($rules);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found']);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized']);
        }

        $post->update($validated);

        $post = [
            "post_id" => $post->id,
            "title" => $post->title,
            "content" => $post->content,
            "created_at" => $post->created_at,
            "updated_at" => $post->updated_at
        ];

        return response()->json([
            'message' => 'Post updated successfully',
            'updated' => $post
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'post  not found']);
        }
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized']);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
