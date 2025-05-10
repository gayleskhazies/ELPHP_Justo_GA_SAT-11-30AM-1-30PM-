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
       $posts = Post::with('user.comments')->get();

        $formattedPosts = $posts->map(function ($post) {
            return [
                "post_id"    => $post->id,
                "title"      => $post->title,
                "content"    => $post->content,
                "author"     => $post->user->username,
                "created_at" => $post->created_at,
                "comments"   => $post->comments->map(function ($comment) {
                    return [
                        "comment_id" => $comment->id,
                        "commentor"  => $comment->user->username,
                        "comment"    => $comment->content,
                    ];
                }),
            ];
        });

        return response()->json(['Feed' => $formattedPosts]);

    }

    public function store(Request $request)
    {
        $request->validate([
        'title'   => 'required',
        'content' => 'required',
        ]);

        $postData = [
            "user_id" => auth()->id(),
            'title'   => $request->title,
            'content' => $request->content,
        ];

        $newPost = Post::create($postData);

        return response()->json([
            'message' => 'Post created successfully',
            'post'    => [
                'title'   => $newPost->title,
                'content' => $newPost->content,
            ]
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::with(['user', 'comments.user'])->find($id);

        if (!$post) {
            return response()->json(["message" => "post not found"]);
        }

        $postData = [
            "post_id" => $post->id,
            "title" => $post->title,
            "content" => $post->content,
            "author" => $post->user->username,
            "created_at" => $post->created_at->toDateTimeString(),
            "comments" => $post->comments->map(fn($comment) => [
                "comment_id" => $comment->id,
                "commentor" => $comment->user->username,
                "comment" => $comment->content
            ])
        ];

        return response()->json($postData);

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

        if (count($rules) === 0) {
            return response()->json(['message' => 'You must provide either a title or content']);
        }

        $validatedData = $request->validate($rules);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'The requested post could not be found']);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'You do not have permission to edit this post']);
        }

        $post->update($validatedData);

        return response()->json([
            'message' => 'Post updated successfully',
            'updated_post' => [
                "id" => $post->id,
                "title" => $post->title,
                "content" => $post->content,
                "created_at" => $post->created_at,
                "updated_at" => $post->updated_at
            ]
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'The post could not be found']);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this post']);
        }

        $post->delete();

        return response()->json(['message' => 'Post successfully deleted']);

    }
}
