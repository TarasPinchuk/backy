<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class CompanyAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/company/register",
     *     summary="Регистрация новой компании",
     *     tags={"Company"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="MyCompany"),
     *             @OA\Property(property="email", type="string", format="email", example="company@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Успешная регистрация компании",
     *         @OA\JsonContent(
     *             @OA\Property(property="company", type="object"),
     *             @OA\Property(property="access_token", type="string", example="Bearer token..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:companies,name',
            'email' => 'required|email|unique:companies,email',
            'password' => 'required|string|min:4'
        ]);

        $company = Company::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $company->createToken('company_token')->plainTextToken;

        return response()->json([
            'company' => $company,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/company/me",
     *     summary="Получить текущую компанию",
     *     tags={"Company"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Профиль текущей компании",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
