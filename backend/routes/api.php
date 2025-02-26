<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::options('{any}', function () {
    return response()->json([], 204);
})->where('any', '.*');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

