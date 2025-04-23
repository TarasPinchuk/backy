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
     * История всех выданных бонусов компанией
     *
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
     *             @OA\Property(
     *                 property="bonuses",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BonusClaim")
     *             )
     *         )
     *     )
     * )
     */
    public function companyHistory()
    {
        $companyId = Auth::guard('company')->id();

        $history = BonusClaim::with('bonus')
            ->whereHas('bonus', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->get();

        // Обёртка под ключом "bonuses"
        return response()->json([
            'bonuses' => $history,
        ], 200);
    }

    /**
     * История полученных бонусов пользователем
     *
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
     *             @OA\Property(
     *                 property="bonuses",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BonusClaim")
     *             )
     *         )
     *     )
     * )
     */
    public function userHistory()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $volunteer = VolunteerRecipient::where('user_id', $user->id)->first();

        if (! $volunteer) {
            return response()->json([
                'bonuses' => [],
            ], 200);
        }

        $history = BonusClaim::with('bonus')
            ->where('volunteer_recipient_id', $volunteer->id)
            ->get();

        return response()->json([
            'bonuses' => $history,
        ], 200);
    }
}
