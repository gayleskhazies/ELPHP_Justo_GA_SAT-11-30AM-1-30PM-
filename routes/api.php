<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CommentController;


use App\Models\Post;
use App\Models\User;



Route::POST('/login', [SessionController::class, 'store']);
Route::POST('/register', [SessionController::class, 'create']);


# Public routes
Route::GET('/posts', [PostController::class, 'index']);

Route::GET('/posts/title/{title}', function ($title) {
   $post = Post::with(['user.comments.user'])
        ->where('title', 'LIKE', "%{$title}%")
        ->get();

    $formattedPosts = $post->map(function ($post) {
        return [
            "post_id"   => $post->id,
            "title"     => $post->title,
            "content"   => $post->content,
            "author"    => $post->user->username,
            "created_at"=> $post->created_at->toDateTimeString(),
            "comments"  => $post->comments->map(function ($comment) {
                return [
                    "comment_id" => $comment->id,
                    "commentor"  => $comment->user->username,
                    "comment"    => $comment->content,
                ];
            })
        ];
    });

    return response()->json(["search_results" => $formattedPosts]);

});

Route::GET('/posts/username/{username}', function ($username) {
   $user = User::with('posts.comments')
        ->where('username', $username)
        ->first();

    if (!$user) {
        return response()->json(['message' => 'Author not found']);
    }

    $formattedPosts = $user->posts->map(function ($post) {
        return [
            "post_id"   => $post->id,
            "title"     => $post->title,
            "content"   => $post->content,
            "created_at"=> $post->created_at,
            "comments"  => $post->comments->map(function ($comment) {
                return [
                    "comment_id" => $comment->id,
                    "commentor"  => $comment->user->username,
                    "content"    => $comment->content,
                ];
            })
        ];
    });

    return response()->json([
        "author" => $user->username,
        "posts"  => $formattedPosts
    ]);

});

#to avoid conflict:  1 recent, 2 post by id arrangement
Route::GET('/posts/recent', function () {
    $post = Post::with(['user.comments.user'])->latest()->get();

    $formattedPosts = $post->map(function ($post) {
        return [
            "post_id"   => $post->id,
            "title"     => $post->title,
            "content"   => $post->content,
            "author"    => $post->user->username,
            "created_at"=> $post->created_at,
            "comments"  => $post->comments->map(function ($comment) {
                return [
                    "comment_id" => $comment->id,
                    "commentor"  => $comment->user->username,
                    "content"    => $comment->content,
                ];
            })
        ];
    });

    return response()->json([
        "recent_feed" => $formattedPosts
    ]);

});
Route::GET('/posts/{id}', [PostController::class, 'show']);




############### Protected Routes

# Post 
Route::middleware('auth:sanctum')->group(function () {
    Route::POST('/posts', [PostController::class, 'store']);
    Route::PATCH('/posts/{id}', [PostController::class, 'update']);
    Route::DELETE('/posts/{id}', [PostController::class, 'destroy']);
});


# session
Route::middleware('auth:sanctum')->group(
    function () {
        Route::GET('/status', function (Request $request) {
            return response()->json([
                'message' => 'You are authenticated',
                'user' => $request->user()
            ]);
        });
        Route::POST('/logout',  [SessionController::class, 'destroy']);
    }
);

# view user
Route::middleware('auth:sanctum')->group(
    function () {

        Route::GET('/users', [ProfileController::class, 'index']);
        Route::GET('/users/{username}', [ProfileController::class, 'show']);
    }
);

# account
Route::middleware('auth:sanctum')->group(function () {

    Route::GET('/me', [AccountController::class, 'index']);
    Route::PATCH('/me', [AccountController::class, 'edit']);
    Route::PATCH('/me/password',  [AccountController::class, 'update']);
    Route::DELETE('/me',  [AccountController::class, 'destroy']);
});

# comment
Route::middleware('auth:sanctum')->group(function () {

    Route::POST('/posts/{post_id}/comments', [CommentController::class, 'store']);
    Route::PATCH('/comments/{id}', [CommentController::class, 'update']);

    Route::DELETE('/comments/{id}', [CommentController::class, 'destroy']);
});
