<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Получить профиль текущего пользователя",
     *     security={{"sanctum": {}}},
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="Успешно, возвращает данные пользователя",
     *         @OA\JsonContent(ref="#/components/schemas/UserDTO")
     *     ),
     *     @OA\Response(response=401, description="Ошибка Unauthorized")
     * )
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        $userDTO = $this->userService->getUserProfile($user->id);
        return response()->json($userDTO);
    }
}
