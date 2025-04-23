<?php

namespace App\Http\Controllers;

use App\Models\BonusClaim;
use App\Models\VolunteerRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class BonusClaimsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/company/bonuses/history",
     *     operationId="getCompanyHistory",
     *     summary="История всех выданных бонусов (компания)",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="История выдачи бонусов компанией",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BonusClaim")
     *         )
     *     )
     * )
     */
    public function companyHistory()
    {
        // Берём ID аутентифицированной компании
        $companyId = Auth::guard('company')->id();

        // Загружаем все выдачи бонусов, относящиеся к бонусам этой компании
        $history = BonusClaim::with('bonus')
            ->whereHas('bonus', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->get();

        return response()->json($history, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/bonuses/history",
     *     operationId="getUserHistory",
     *     summary="История полученных бонусов (пользователь)",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="История получения бонусов пользователем",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BonusClaim")
     *         )
     *     )
     * )
     */
    public function userHistory()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Находим запись волонтёра, привязанную к этому пользователю
        $volunteer = VolunteerRecipient::where('user_id', $user->id)->first();

        // Если такой записи нет — вернём пустой массив
        if (! $volunteer) {
            return response()->json([], 200);
        }

        // Загружаем историю только для этого volunteer_recipient_id
        $history = BonusClaim::with('bonus')
            ->where('volunteer_recipient_id', $volunteer->id)
            ->get();

        return response()->json($history, 200);
    }
}
