<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\BonusClaim;
use App\Models\VolunteerRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class BonusController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/company/bonuses",
     *     operationId="createBonus",
     *     summary="Создание бонуса компанией",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","level"},
     *             @OA\Property(property="name",  type="string", example="Скидка 10%"),
     *             @OA\Property(property="level", type="string", example="max", enum={"max","medium","min"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Бонус успешно создан", @OA\JsonContent(ref="#/components/schemas/Bonus")),
     *     @OA\Response(response=400, description="Неправильные данные"),
     *     @OA\Response(response=401, description="Не аутентифицирован"),
     *     @OA\Response(response=403, description="Нет прав")
     * )
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'level' => 'required|in:max,medium,min',
        ]);

        $bonus = Bonus::create([
            'name'       => $validated['name'],
            'level'      => $validated['level'],
            'company_id' => Auth::guard('company')->id(),
            'is_used'    => false,
        ]);

        return response()->json($bonus, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/company/bonuses",
     *     operationId="getCompanyBonuses",
     *     summary="Бонусы текущей компании",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список бонусов компании",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="bonuses",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Bonus")
     *             )
     *         )
     *     )
     * )
     */
    public function companyBonuses()
    {
        $bonuses = Bonus::where('company_id', Auth::guard('company')->id())->get();

        return response()->json([
            'bonuses' => $bonuses,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/bonuses",
     *     operationId="getUserBonuses",
     *     summary="Бонусы текущего пользователя",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список бонусов пользователя",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Bonus")
     *             )
     *         )
     *     )
     * )
     */
    public function userBonuses()
    {
        $user = Auth::user();

        $volunteer = VolunteerRecipient::where('user_id', $user->id)->first();
        if (! $volunteer) {
            return response()->json([
                'user' => [],
            ], 200);
        }

        $bonuses = BonusClaim::with('bonus')
            ->where('volunteer_recipient_id', $volunteer->id)
            ->get()
            ->pluck('bonus');

        return response()->json([
            'user' => $bonuses,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/bonuses",
     *     operationId="getAllBonuses",
     *     summary="Список всех бонусов (только для админов)",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Все бонусы", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Bonus"))),
     *     @OA\Response(response=403, description="Запрещено")
     * )
     */
    public function allBonuses()
    {
        if (! Auth::user() || ! Auth::user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(Bonus::all(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/bonuses/{bonus}/claim",
     *     operationId="claimBonus",
     *     summary="Взять бонус",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="bonus", in="path", required=true, @OA\Schema(type="integer", example=5)),
     *     @OA\Response(response=201, description="Бонус взят", @OA\JsonContent(ref="#/components/schemas/BonusClaim")),
     *     @OA\Response(response=400, description="Бонус уже был использован"),
     *     @OA\Response(response=403, description="Нет прав на этот бонус"),
     *     @OA\Response(response=404, description="Бонус не найден")
     * )
     */
    public function claim(Bonus $bonus)
    {
        $user = Auth::user();

        $volunteer = VolunteerRecipient::where('company_id', $bonus->company_id)
            ->where(function($q) use ($user) {
                $q->where('email', $user->email);
                if ($user->inn) {
                    $q->orWhere('inn', $user->inn);
                }
            })
            ->first();

        if (! $volunteer) {
            return response()->json(['message' => 'Вы не загружены как волонтёр этой компании'], 403);
        }

        if ($bonus->is_used) {
            return response()->json(['message' => 'Этот бонус уже был использован'], 400);
        }

        if (is_null($volunteer->user_id)) {
            $volunteer->update(['user_id' => $user->id]);
        }

        $bonus->update(['is_used' => true]);

        $claim = BonusClaim::create([
            'bonus_id'               => $bonus->id,
            'volunteer_recipient_id' => $volunteer->id,
            'claimed_at'             => now(),
        ]);

        return response()->json($claim, 201);
    }
}
