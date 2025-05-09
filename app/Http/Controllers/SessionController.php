<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SessionController extends Controller
{


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Registered successfully please login',
            'username'    => $user->username,
            'email' => $user->email
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('postman')->plainTextToken;

        return response()->json([
            'message' => 'Logged in',
            'token' => $token,
            'Welcome' => $user->username
        ]);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
