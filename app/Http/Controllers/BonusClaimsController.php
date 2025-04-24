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
     *             @OA\Property(
     *                 property="bonuses",
     *                 type="array",
     *                 @OA\Items(
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/BonusClaim"),
     *                         @OA\Schema(
     *                             @OA\Property(property="bonus", ref="#/components/schemas/Bonus"),
     *                             @OA\Property(property="company", ref="#/components/schemas/Company"),
     *                             @OA\Property(property="volunteerRecipient", ref="#/components/schemas/VolunteerRecipient")
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function companyHistory()
    {
        $companyId = Auth::guard('company')->id();

        $history = BonusClaim::with(['bonus', 'volunteerRecipient'])
            ->whereHas('bonus', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->orderByDesc('claimed_at')
            ->get();

        // Преобразуем к тому виду, который ждёт фронтенд
        $bonuses = $history->map(function (BonusClaim $claim) {
            $vol = $claim->volunteerRecipient;
            return [
                'id'         => $claim->bonus->id,
                'name'       => $claim->bonus->name,
                'level'      => $claim->bonus->level,
                'claimed_at' => $claim->claimed_at,
                'volunteer'  => [
                    'id'           => $vol->id,
                    'full_name'    => $vol->full_name,
                    'inn'          => $vol->inn,
                    'email'        => $vol->email,
                    'access_level' => $vol->access_level,
                ],
            ];
        });

        return response()->json(['bonuses' => $bonuses], 200);
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
            return response()->json(['bonuses' => []], 200);
        }

        $history = BonusClaim::with('bonus')
            ->where('volunteer_recipient_id', $volunteer->id)
            ->get();

        return response()->json(['bonuses' => $history], 200);
    }
}
