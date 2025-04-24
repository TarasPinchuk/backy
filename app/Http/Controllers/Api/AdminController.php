<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\Company;
use App\Models\VolunteerRecipient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AdminController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/admin/volunteers/upload",
     *   summary="Загрузка CSV-файла со списком волонтёров для всех компаний",
     *   tags={"Admin"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="file", type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Волонтёры успешно загружены",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Volunteers uploaded successfully"),
     *       @OA\Property(property="imported", type="integer", example=42)
     *     )
     *   )
     * )
     */
    public function uploadVolunteers(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $rowCount = 0;
        if ($file->isValid() && ($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ",");
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowCount++;
                $name        = $data[0] ?? null;
                $email       = $data[1] ?? null;
                $inn         = $data[2] ?? null;
                $companyName = $data[3] ?? null;
                if (! $email || ! $inn || ! $companyName) {
                    continue;
                }
                $company = Company::firstOrCreate(['name' => $companyName]);
                $exists = VolunteerRecipient::where('company_id', $company->id)
                    ->where(function($q) use ($email, $inn) {
                        $q->where('email', $email)
                          ->orWhere('inn', $inn);
                    })->exists();
                if (! $exists) {
                    VolunteerRecipient::create([
                        'full_name'    => $name,
                        'email'        => $email,
                        'inn'          => $inn,
                        'company_id'   => $company->id,
                    ]);
                }
            }
            fclose($handle);
        }

        return response()->json([
            'message'  => 'Volunteers uploaded successfully',
            'imported' => $rowCount,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/bonuses",
     *   summary="Список всех бонусов",
     *   tags={"Admin"},
     *   @OA\Response(
     *     response=200,
     *     description="Все бонусы",
     *     @OA\JsonContent(
     *       @OA\Property(property="bonuses", type="array", @OA\Items(ref="#/components/schemas/Bonus"))
     *     )
     *   )
     * )
     */
    public function listBonuses(): JsonResponse
    {
        $allBonuses = Bonus::all();
        return response()->json([
            'bonuses' => $allBonuses,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/volunteers",
     *   summary="Список всех волонтёров",
     *   tags={"Admin"},
     *   @OA\Response(
     *     response=200,
     *     description="Все волонтёры",
     *     @OA\JsonContent(
     *       @OA\Property(property="volunteers", type="array", @OA\Items(ref="#/components/schemas/VolunteerRecipient"))
     *     )
     *   )
     * )
     */
    public function listVolunteers(): JsonResponse
    {
        $allVolunteers = VolunteerRecipient::all();
        return response()->json([
            'volunteers' => $allVolunteers,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/companies",
     *   summary="Список всех компаний",
     *   tags={"Admin"},
     *   @OA\Response(
     *     response=200,
     *     description="Все компании",
     *     @OA\JsonContent(
     *       @OA\Property(property="companies", type="array", @OA\Items(ref="#/components/schemas/Company"))
     *     )
     *   )
     * )
     */
    public function listCompanies(): JsonResponse
    {
        $allCompanies = Company::all();
        return response()->json([
            'companies' => $allCompanies,
        ]);
    }
}
