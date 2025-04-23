<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\BonusController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/** Users */
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [RegisteredUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',            [UserController::class, 'profile']);
    Route::get('/bonuses/user',    [BonusController::class, 'userBonuses']);
    Route::get('/bonuses/all',     [BonusController::class, 'allBonuses']);

    Route::post('/bonus/create',   [BonusController::class, 'create']);
});

Route::prefix('company')->group(function () {
    Route::post('/register', [CompanyAuthController::class, 'register']);
    Route::post('/login',    [CompanyAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',       [CompanyAuthController::class, 'me']);
        Route::post('/bonuses', [BonusController::class, 'create']);
        Route::get('/bonuses',  [BonusController::class, 'companyBonuses']);
    });
});
