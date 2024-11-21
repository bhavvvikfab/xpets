<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users', // Validate and ensure uniqueness of username
            'password' => 'required|string|min:8',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 401);
        }

        $request['password'] = Hash::make($request['password']);
        $input = $request->only(['name', 'email', 'username','password']);
        $user = ResponseHelper::addOrEdit(new User, $input);
        // $user = User::create([
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'username' => $validated['username'],
        //     'password' => Hash::make($validated['password']),
        // ]);
    
        $role = isset($request['role']) && $request['role'] == 'admin' ? 'admin' : 'user';
        // $role = 'admin';
        $user->assignRole($role); // Assign user role

        return ResponseHelper::success('User registered successfully', 201);
        // return response()->json(['message' => 'User registered successfully'], 201);
    }    

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string', // Validate username instead of email
            'password' => 'required|string',
        ]);
    
        // Attempt to log in using username and password
        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            $user->token = $user->createToken('API Token')->accessToken; // Generate access token

            return ResponseHelper::success('User Login successfully', 201,$user);
            return response()->json(['user' => $user], 200);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }    

}
