<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\VoyageController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('admins', AdminController::class);
Route::apiResource('clients', ClientController::class);
Route::apiResource('partenaires', PartenaireController::class);
Route::apiResource('voyages', VoyageController::class);
Route::get('voyages/search', [VoyageController::class, 'search']);
Route::apiResource('reservations', ReservationController::class);
Route::post('/reservations/reserve', [ReservationController::class, 'reserve']);
// Routes publiques pour l'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées nécessitant un token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']); // Récupérer l'utilisateur connecté
    Route::post('/logout', [AuthController::class, 'logout']);
});
