<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check credentials
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate token
        $token = $request->user()->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $request->user(),
        ], 200);
    }
}

