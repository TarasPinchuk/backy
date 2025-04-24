<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CompanyAuthController;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;

use App\Http\Controllers\BonusController;
use App\Http\Controllers\BonusClaimsController;
use App\Http\Controllers\CompaniesController;

/*
|--------------------------------------------------------------------------
| Волонтёры (пользовательские маршруты)
|--------------------------------------------------------------------------
*/

// регистрация и логин волонтёра
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [AuthenticatedSessionController::class, 'store']);

// вернуть профиль текущего волонтёра (по bearer-токену)
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'profile']);

// все прочие маршруты волонтёра
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // профиль (альтернативный путь)
    Route::get('profile',         [UserController::class, 'profile']);
    // выход
    Route::post('logout',         [AuthenticatedSessionController::class, 'destroy']);

    // все активированные бонусы пользователя (DESC по времени)
    Route::get('bonuses',         [BonusController::class, 'index']);
    // доступные для активации по email
    Route::get('bonuses/available',[BonusController::class, 'available']);
    // доступные бонусы по введённому ИНН
    Route::get('bonuses/by-inn',   [BonusController::class, 'byInn']);
    // активация бонуса
    Route::post('bonuses/{bonus}/claim', [BonusController::class, 'claim']);
    // история полученных бонусов
    Route::get('bonuses/history', [BonusClaimsController::class, 'userHistory']);
});

/*
|--------------------------------------------------------------------------
| Компании
|--------------------------------------------------------------------------
*/
Route::prefix('company')->group(function () {
    // регистрация / логин компании
    Route::post('register',         [CompanyAuthController::class, 'register']);
    Route::post('login',            [CompanyAuthController::class, 'login']);

    // все остальные маршруты — только для аутентифицированных компаний
    Route::middleware('auth:sanctum')->group(function () {
        // профиль компании
        Route::get('me',            [CompanyAuthController::class, 'me']);
        // волонтёры своей компании
        Route::get('volunteers',          [CompaniesController::class, 'volunteers']);
        Route::post('volunteers/upload',  [CompaniesController::class, 'uploadVolunteers']);
        // создание бонуса
        Route::post('bonuses',            [BonusController::class, 'create']);
        // список бонусов компании
        Route::get('bonuses',             [BonusController::class, 'companyBonuses']);
        // история выдачи бонусов компанией
        Route::get('bonuses/history',     [BonusClaimsController::class, 'companyHistory']);
    });
});

/*
|--------------------------------------------------------------------------
| Админка (доступ по admin-токену)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // загрузка CSV со всеми волонтёрами (для всех компаний)
    Route::post('volunteers/upload',  [AdminController::class, 'uploadVolunteers']);
    // список всех бонусов
    Route::get('bonuses',             [AdminController::class, 'listBonuses']);
    // список всех волонтёров
    Route::get('volunteers',          [AdminController::class, 'listVolunteers']);
    // список всех компаний
    Route::get('companies',           [AdminController::class, 'listCompanies']);
});
