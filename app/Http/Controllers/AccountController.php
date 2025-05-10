<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{

    public function index()
    {
     $user = User::with('posts.comments.user')->find(auth()->id());

        $profileData = [
            "username" => $user->username,
            "email" => $user->email,
            "created_at" => $user->created_at,
        ];

        $postsData = $user->posts->map(function ($post) {
            return [
                "post_id"   => $post->id, 
                "title"     => $post->title,
                "content"   => $post->content,
                "created_at"=> $post->created_at,
                "comments"  => $post->comments->map(fn($comment) => [
                    "comment_id" => $comment->id,
                    "commentor"  => $comment->user->username,
                    "content"    => $comment->content,
                ])
            ];
        });

        return response()->json([
            "profile" => $profileData,
            "My Post" => $postsData,
        ]);

    }





    public function edit(Request $request)
    {
       $request->validate([
            'username' => 'required',
            'email'    => 'required',
        ]);

        $user = User::findOrFail(auth()->id());

        $user->update([
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        return response()->json([
            'message' => 'Your account has been updated successfully',
            'user'    => $user
        ]);

    }


    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'The current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Your password has been updated successfully']);

    }


    public function destroy(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'The provided password is incorrect']);
        }

        $user = User::findOrFail(auth()->id());
        $user->delete();
        $user->tokens()->delete();

        return response()->json(['message' => 'Your account has been deleted successfully']);

    }
}
