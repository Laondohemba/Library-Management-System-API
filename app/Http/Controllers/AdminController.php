<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'min:3', 'max:255'],
                'email' => ['required', 'max:255', 'email', 'unique:admins'],
                'phone' => ['required', 'numeric'],
                'password' => ['required', 'min:8'],
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $admin = Admin::create($validated);
            if($admin)
            {
                $token = JWTAuth::fromUser($admin);

                return response()->json([
                    'message' => "Account created successfully",
                    'token' => $token,
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            
            return response()->json([
                'errors' => $e->errors(),
            ]);
        }
    }

    public function login(Request $request){
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
            $token = auth('admin')->attempt($validated);

            if($token)
            {
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                ]);
            }

            return response()->json([
                'errors' => 'Invalid email or password. Note that passwords are case sensitive',
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
                'message' => 'Logout successful'
            ]);
        }
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
