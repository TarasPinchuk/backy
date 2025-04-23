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
     *     summary="Создание бонуса",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Скидка 10%")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Бонус создан")
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $actor = $request->user();

        $bonus = Bonus::create([
            'name'       => $request->name,
            'company_id' => method_exists($actor, 'createToken')
                              ? $actor->id  
                              : null,
            'user_id'    => method_exists($actor, 'createToken')
                              ? null      
                              : $actor->id 
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
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id",          type="integer", format="int64",        example=1),
 *                 @OA\Property(property="name",        type="string",                        example="Скидка 10%"),
 *                 @OA\Property(property="company_id",  type="integer", format="int64",        example=3),
 *                 @OA\Property(property="user_id",     type="integer", format="int64",        nullable=true, example=null),
 *                 @OA\Property(property="is_used",     type="boolean",                       example=false),
 *                 @OA\Property(property="created_at",  type="string",  format="date-time",  example="2025-04-23T15:47:55.000000Z"),
 *                 @OA\Property(property="updated_at",  type="string",  format="date-time",  example="2025-04-23T15:47:55.000000Z")
 *             )
 *         )
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
