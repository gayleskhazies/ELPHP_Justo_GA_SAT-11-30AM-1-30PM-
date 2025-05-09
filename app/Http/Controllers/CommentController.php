<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class CommentController extends Controller
{



    public function store(Request $request, $post_id)
    {
        $request->validate([
            'content' => 'required'
        ]);

        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found']);
        }

        $attributes = [
            'user_id' => auth()->id(),
            'post_id' => $post_id,
            'content' => $request->content


        ];
        $comment = Comment::create($attributes);
        $comment = [
            "title" => $post->title,
            "comment" => $comment->content
        ];

        return response()->json([
            'message' => 'Comment created successfully',
            'created_comment' => $comment
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'post_id'  => 'required|integer',
            'content'  => 'required|string'
        ]);
        $comment = Comment::with('post')->find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found']);
        }

        if (!$comment->post || $comment->post->id !== $request->post_id) {
            return response()->json(['message' => 'Comment does not belong to this post']);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized']);
        }

        $old_comment = $comment->content;

        $comment->update([
            'content' => $request->content,
        ]);

        $comment = [
            'title' => $comment->post->title,
            'old_comment' => $old_comment,
            'updated_comment' => $comment->content
        ];


        return response()->json([
            'message' => 'Comment updated successfully',
            'updated_comment' => $comment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment  not found']);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized']);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
            'comment_id' => $comment->id
        ]);
    }
}
