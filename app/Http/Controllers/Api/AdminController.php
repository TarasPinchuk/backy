<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\Company;
use App\Models\VolunteerRecipient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/volunteers/upload",
     *     summary="Загрузка CSV со списком волонтёров (админ)",
     *     tags={"Admin"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="CSV обработан", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Volunteers uploaded successfully"),
     *         @OA\Property(property="imported", type="integer", example=42)
     *     ))
     * )
     */
    public function uploadVolunteers(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file     = $request->file('file');
        $imported = 0;

        if ($file->isValid() && ($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Считаем заголовок и пропускаем
            fgetcsv($handle, 1000, ',');

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                // Ожидаем: [0]=full_name, [1]=email, [2]=inn, [3]=company_name
                [$fullName, $email, $inn, $companyName] = array_pad($row, 4, null);

                if (! $fullName || ! $email || ! $inn || ! $companyName) {
                    continue;
                }

                // **Только существующие компании!** Если нет — пропускаем
                $company = Company::where('name', $companyName)->first();
                if (! $company) {
                    continue;
                }

                // Проверяем нет ли уже такого волонтёра в этой компании
                $exists = VolunteerRecipient::where('company_id', $company->id)
                    ->where(function($q) use ($email, $inn) {
                        $q->where('email', $email)
                          ->orWhere('inn', $inn);
                    })
                    ->exists();

                if (! $exists) {
                    VolunteerRecipient::create([
                        'company_id'   => $company->id,
                        'full_name'    => $fullName,
                        'email'        => $email,
                        'inn'          => $inn,
                        // остальные поля CSV (phone, birth_date, achievements, access_level) можно тоже добавить по потребности
                    ]);
                    $imported++;
                }
            }
            fclose($handle);
        }

        return response()->json([
            'message'  => 'Volunteers uploaded successfully',
            'imported' => $imported,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/bonuses",
     *     summary="Все бонусы (админ)",
     *     tags={"Admin"},
     *     @OA\Response(response=200, description="Список бонусов", @OA\JsonContent(
     *         @OA\Property(property="bonuses", type="array", @OA\Items(ref="#/components/schemas/Bonus"))
     *     ))
     * )
     */
    public function listBonuses(): JsonResponse
    {
        return response()->json(['bonuses' => Bonus::all()]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/volunteers",
     *     summary="Все волонтёры (админ)",
     *     tags={"Admin"},
     *     @OA\Response(response=200, description="Список волонтёров", @OA\JsonContent(
     *         @OA\Property(property="volunteers", type="array", @OA\Items(ref="#/components/schemas/VolunteerRecipient"))
     *     ))
     * )
     */
    public function listVolunteers(): JsonResponse
    {
        return response()->json(['volunteers' => VolunteerRecipient::all()]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/companies",
     *     summary="Все компании (админ)",
     *     tags={"Admin"},
     *     @OA\Response(response=200, description="Список компаний", @OA\JsonContent(
     *         @OA\Property(property="companies", type="array", @OA\Items(ref="#/components/schemas/Company"))
     *     ))
     * )
     */
    public function listCompanies(): JsonResponse
    {
        return response()->json(['companies' => Company::all()]);
    }
}
