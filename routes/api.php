<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\BonusController;

/**
 * -----------------------------------------------------------
 * Пользовательские маршруты
 * -----------------------------------------------------------
 */

/**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Регистрация пользователя",
 *     tags={"Auth"}
 * )
 */
Route::post('/register', [RegisteredUserController::class, 'store']);

/**
 * @OA\Post(
 *     path="/api/login",
 *     summary="Логин пользователя",
 *     tags={"Auth"}
 * )
 */
Route::post('/login', [RegisteredUserController::class, 'login']);

/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="Профиль пользователя",
 *     tags={"User"},
 *     security={{"sanctum":{}}}
 * )
 */
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'profile']);

Route::middleware('auth:sanctum')->group(function () {
    /**
     * @OA\Get(
     *     path="/api/bonuses/user",
     *     summary="Бонусы пользователя",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}}
     * )
     */
    Route::get('/bonuses/user', [BonusController::class, 'userBonuses']);

    /**
     * @OA\Get(
     *     path="/api/bonuses/all",
     *     summary="Все бонусы (только для админов)",
     *     tags={"Bonus"},
     *     security={{"sanctum":{}}}
     * )
     */
    Route::get('/bonuses/all', [BonusController::class, 'allBonuses']);
});

/**
 * -----------------------------------------------------------
 * Маршруты компаний
 * -----------------------------------------------------------
 */

Route::prefix('company')->group(function () {
    /**
     * @OA\Post(
     *     path="/api/company/register",
     *     summary="Регистрация компании",
     *     tags={"Company"}
     * )
     */
    Route::post('/register', [CompanyAuthController::class, 'register']);

    /**
     * @OA\Post(
     *     path="/api/company/login",
     *     summary="Логин компании",
     *     tags={"Company"}
     * )
     */
    Route::post('/login', [CompanyAuthController::class, 'login']);

    Route::middleware('auth.company')->group(function () {
        /**
         * @OA\Get(
         *     path="/api/company/me",
         *     summary="Профиль компании",
         *     tags={"Company"},
         *     security={{"sanctum":{}}}
         * )
         */
        Route::get('/me', [CompanyAuthController::class, 'me']);

        /**
         * @OA\Post(
         *     path="/api/company/bonuses",
         *     summary="Создание бонуса компанией",
         *     tags={"Bonus"},
         *     security={{"sanctum":{}}}
         * )
         */
        Route::post('/bonuses', [BonusController::class, 'create']);

        /**
         * @OA\Get(
         *     path="/api/company/bonuses",
         *     summary="Получение бонусов компании",
         *     tags={"Bonus"},
         *     security={{"sanctum":{}}}
         * )
         */
        Route::get('/bonuses', [BonusController::class, 'companyBonuses']);
    });
});
