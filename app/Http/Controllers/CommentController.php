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
            'content' => 'required',
        ]);

        $post = Post::find($post_id);

        if (!$post) {
            return response()->json(['message' => 'The specified post was not found']);
        }

        $commentData = [
            'user_id' => auth()->id(),
            'post_id' => $post_id,
            'content' => $request->content,
        ];

        $newComment = Comment::create($commentData);

        $commentResponse = [
            "title"   => $post->title,
            "comment" => $newComment->content,
        ];

        return response()->json([
            'message'        => 'Comment has been successfully created',
            'created_comment' => $commentResponse,
        ]);

    }


    public function update(Request $request, $id)
    {
       $request->validate([
            'post_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        $comment = Comment::with('post')->find($id);

        if (!$comment) {
            return response()->json(['message' => 'The specified comment was not found']);
        }

        if (!$comment->post || $comment->post->id !== $request->post_id) {
            return response()->json(['message' => 'This comment does not belong to the specified post']);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to edit this comment']);
        }

        $previousCommentContent = $comment->content;

        $comment->update([
            'content' => $request->content,
        ]);

        $updatedComment = [
            'title'            => $comment->post->title,
            'previous_comment' => $previousCommentContent,
            'updated_comment'  => $comment->content,
        ];

        return response()->json([
            'message'        => 'Comment has been updated successfully',
            'updated_comment' => $updatedComment,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'The comment could not be found']);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this comment']);
        }

        $comment->delete();

        return response()->json([
            'message'    => 'The comment has been successfully deleted',
            'comment_id' => $comment->id,
        ]);

    }
}
