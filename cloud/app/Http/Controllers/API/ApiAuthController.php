<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class ApiAuthController extends Controller
{
    use HasApiTokens;

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'success' => true,
            'code' => 201,
            'message' => 'Success',
            'token' => $token,
        ], 201);
    }

    public function authorization(LoginUserRequest $request): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Authorization failed'
            ], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Success',
            'token' => $token
        ], 200);

    }

    public function logout(Request $request): JsonResponse
    {

        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([], 204);
    }
}
