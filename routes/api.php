<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\BonusClaimsController;

/*
|--------------------------------------------------------------------------
| Волонтёры
|--------------------------------------------------------------------------
*/
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/user',     [UserController::class, 'profile']);

    // бонусы текущего пользователя
    Route::get('/user/bonuses',         [BonusController::class, 'userBonuses']);
    // доступные бонусы
    Route::get('/bonuses/available',    [BonusController::class, 'available']);
    // взять бонус
    Route::post('/bonuses/{bonus}/claim', [BonusController::class, 'claim']);
    // история полученных бонусов (волонтёр)
    Route::get('/bonuses/history',      [BonusClaimsController::class, 'userHistory']);
});

/*
|--------------------------------------------------------------------------
| Компании
|--------------------------------------------------------------------------
*/
Route::prefix('company')->group(function () {
    // регистрация/логин без токена
    Route::post('/register', [CompanyAuthController::class, 'register']);
    Route::post('/login',    [CompanyAuthController::class, 'login']);

    // всё остальное — только для аутентифицированных компаний
    Route::middleware('auth:sanctum')->group(function () {
        // профиль компании
        Route::get('/me', [CompanyAuthController::class, 'me']);

        // волонтёры
        Route::get('/volunteers',         [CompaniesController::class, 'volunteers']);
        Route::post('/volunteers/upload', [CompaniesController::class, 'uploadVolunteers']);

        // создание бонуса
        Route::post('/bonuses',          [BonusController::class, 'create']);
        // список бонусов текущей компании
        Route::get('/bonuses',           [BonusController::class, 'companyBonuses']);
        // история выдачи бонусов компанией
        Route::get('/bonuses/history',   [BonusClaimsController::class, 'companyHistory']);
    });
});
