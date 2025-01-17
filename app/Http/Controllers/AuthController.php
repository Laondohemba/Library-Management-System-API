<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'min:3', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users'],
                'phone' => ['required'],
                'password' => ['required', 'min:8'],
            ]);

            $user = User::create($validated);
            
            if($user)
            {
                $token = JWTAuth::fromUser($user);

                return response()->json([
                    'message' => 'Account created successfully',
                    'token' => $token,
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            
            return response()->json([
                'errors' => $e->errors(),
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if($token = JWTAuth::attempt($validated))
            {
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                ]);
            }

            return response()->json([
                'errors' => 'Invalid email or password. Note that passwords are case sensitive'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            
            return response()->json([
                'errors' => $e->errors(),
            ]);
        }
    }

    public function logout()
    {
        $token = JWTAuth::getToken();

        if(JWTAuth::invalidate($token))
        {
            return response()->json([
                'message' => 'Successfully logged out',
            ]);
        }
    }
}
