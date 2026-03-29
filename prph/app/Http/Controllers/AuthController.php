<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * REGISTER a new user
     * POST /api/register
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            // will add "|confirmed" later to confirm password
        ]);

        // Create user
        $user = User::create([
            'u_name' => $request->name,
            'email' => $request->email,
            // Hash password before saving
            'password' => Hash::make($request->password),
        ]);

        // Generate token for user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response
        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * LOGIN user
     * POST /api/login
     */
    public function login(Request $request)
    {
        // Validate inputs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'user' => [
                'user_id' => $user->user_id,
                'u_name' => $user->u_name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_pic' => $user->profile_pic,
            ],
            'token' => $token
        ]);
    }

    /**
     * LOGOUT user (invalidate token)
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        // Revoke all tokens for current user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully!'
        ]);
    }

    /**
     * Get CURRENT authenticated user
     * GET /api/user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateUser(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Adjust column if your users table uses 'u_name' instead of 'name'
        $user->update(['u_name' => $request->name]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
