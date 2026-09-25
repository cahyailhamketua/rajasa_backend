<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            (int) $request->input('per_page', 10),
            100
        );

        $users = $this->userService->getUsers($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data' => $users,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Detail user berhasil diambil.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse 
    {
        Gate::authorize('update', $user);

        $user = $this->userService->updateUser(
            $user,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diperbarui.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }
}