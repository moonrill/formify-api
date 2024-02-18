<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request The HTTP request
     * @throws JsonResponse When validation fails
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string'],
            'password' => ['required', 'string', 'min:5'],
            'email'    => ['required', 'email', 'string', 'unique:users,email'],
        ]);

        // Check if validation fails
        if ($validator->fails())
        {
            // Throw validation error response
            return response()->json([
                'message' => 'Invalid fields',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create new user
        $user = User::query()->create([
            'name'     => $request->name,
            'password' => $request->password,
            'email'    => $request->email,
        ]);

        // Hash user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Return success 
        return response()->json([
            'message' => 'Register success',
            'data'    => $user,
        ], 201);
    }

    /**
     * Handle user login
     *
     * @param  Request  $request The HTTP request
     * @throws JsonResponse When validation fails
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', 'string'],
            'password' => ['required', 'string', 'min:5'],
        ]);

        // Check if validation fails
        if ($validator->fails())
        {
            // Throw validation error response
            return response()->json([
                'message' => 'Invalid fields',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Check if user exists
        if (Auth::attempt($request->only('email', 'password')))
        {
            // Get user data
            $user = Auth::user();
            // Generate access token
            $token = $user->createToken('AuthToken')->plainTextToken;

            // Return success response
            return response()->json([
                'message' => 'Login success',
                'user'    => [
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'accessToken' => $token,
                ],
            ], 201);
        }

        // Return error message if login fails
        return response()->json([
            'message' => 'Email or password incorrect',
        ], 401);
    }

    /**
     * Log the user out and delete all the user's tokens.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Delete user tokens
        Auth::user()->tokens()->delete();

        // Return success response
        return response()->json([
            'message' => 'Logout success',
        ], 200);
    }
}
