<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class BonusController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/bonus/create",
     *     summary="Создание бонуса компанией",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Скидка 10%")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Бонус создан",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $bonus = Bonus::create([
            'name' => $request->name,
            'company_id' => Auth::guard('company')->id(),
        ]);

        return response()->json($bonus, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/company/bonuses",
     *     summary="Бонусы текущей компании",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список бонусов компании",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function companyBonuses()
    {
        $bonuses = Bonus::where('company_id', Auth::guard('company')->id())->get();
        return response()->json($bonuses);
    }

    /**
     * @OA\Get(
     *     path="/api/user/bonuses",
     *     summary="Бонусы текущего пользователя",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список бонусов пользователя",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function userBonuses()
    {
        $bonuses = Bonus::where('user_id', Auth::id())->get();
        return response()->json($bonuses);
    }

    /**
     * @OA\Get(
     *     path="/api/bonuses",
     *     summary="Список всех бонусов (только для админов)",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Все бонусы",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещен"
     *     )
     * )
     */
    public function allBonuses()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(Bonus::all());
    }
}
