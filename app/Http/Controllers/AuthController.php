<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * A description of the entire PHP function.
     *
     * @param Request $request description
     * @throws JsonResponse validation error
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string'],
            'password' => ['required', 'string'],
            'email'    => ['required', 'email', 'string', 'unique:users,email'],
        ]);

        // If validation fails
        if ($validator->fails())
        {
            // Throw validation error
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 400);
        }

        // Create user
        $user = User::query()->create([
            'name'     => $request->name,
            'password' => $request->password,
            'email'    => $request->email,
        ]);

        // Hash user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Send response
        return response()->json([
            'message' => 'Register success',
            'data'    => $user,
        ], 201);
    }

    public function login()
    {

    }

    public function logout()
    {

    }
}
