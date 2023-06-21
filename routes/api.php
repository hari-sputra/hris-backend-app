<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\ResponsibilityController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\TeamController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    // auth
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('user', [UserController::class, 'fetch']);

    // company
    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'all']);
        Route::post('/', [CompanyController::class, 'create']);
        Route::post('/{id}', [CompanyController::class, 'update']);
    });

    // Team
    Route::prefix('team')->group(function () {
        Route::get('/', [TeamController::class, 'all']);
        Route::post('/', [TeamController::class, 'create']);
        Route::post('/{id}', [TeamController::class, 'update']);
        Route::delete('/{id}', [TeamController::class, 'destroy']);
    });

    // Role
    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'all']);
        Route::post('/', [RoleController::class, 'create']);
        Route::post('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    // Role
    Route::prefix('responsibility')->group(function () {
        Route::get('/', [ResponsibilityController::class, 'all']);
        Route::post('/', [ResponsibilityController::class, 'create']);
        Route::delete('/{id}', [ResponsibilityController::class, 'destroy']);
    });

    // Route::get('/test/{id}', [ResponsibilityController::class, 'test']);
});
