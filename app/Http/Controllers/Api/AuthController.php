<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => [
                    'Username atau password salah.',
                ],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Register new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User berhasil didaftarkan.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    /**
     * Change authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'Password saat ini salah.',
                ],
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }

    /**
     * Reset user's password to default password.
     */
    public function resetPassword(User $user): JsonResponse
    {
        $defaultPassword = match ($user->role) {
            'admin' => 'admin123',
            'super_admin' => 'superadmin123',
            default => null,
        };

        if ($defaultPassword === null) {
            return response()->json([
                'success' => false,
                'message' => 'Role user tidak valid untuk reset password.',
            ], 422);
        }

        $user->update([
            'password' => $defaultPassword,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password user berhasil direset ke password default.',
            'data' => [
                'user' => $user,
                'default_password' => $defaultPassword,
            ],
        ]);
    }
}