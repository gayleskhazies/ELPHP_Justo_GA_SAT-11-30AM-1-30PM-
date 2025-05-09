<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        $all = $users->map(function ($user) {
            return [
                "username" => $user->username,
                "created_at" => $user->created_at
            ];
        });

        return response()->json(
            [
                "registered users" => $all
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Display the specified resource.
     */
    public function show($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        $user = [
            "username" => $user->username,
            "created_at" => $user->created_at
        ];


        return response()->json(
            ["registered user" => $user]
        );
    }
}
