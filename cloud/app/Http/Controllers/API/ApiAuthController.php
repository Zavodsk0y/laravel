<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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

    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->json()->all(); // Получаем все данные из запроса
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:3|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'first_name' => 'required|min:2',
                'last_name' => 'required',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => $errors,
            ], 422);
        }

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

    public function authorization(Request $request)
    {
        try {
            $data = $request->json()->all();
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => $errors,
            ], 422);
        }

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Authorization failed'
            ]);
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

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([], 204);
    }
}
