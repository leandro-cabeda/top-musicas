<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MusicaController;
use App\Http\Controllers\SugestaoController;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\SubController;


// "/api/documentation" => aqui é url para visualizar swagger


Route::get('/musicas', [MusicaController::class, 'index']);
Route::get(
    '/', [SubController::class, 'home']
);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/musicas/sugerir', [SugestaoController::class, 'sugerir']);
    Route::get('/musicas/sugestoes', [SugestaoController::class, 'listar']);
    Route::get('/musicas/{id}', [MusicaController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/musicas', [MusicaController::class, 'store']);
    Route::put('/musicas/{id}', [MusicaController::class, 'update']);
    Route::delete('/musicas/{id}', [MusicaController::class, 'destroy']);

    Route::patch('/musicas/sugestoes/{id}/aprovar', [SugestaoController::class, 'aprovar']);
    Route::patch('/musicas/sugestoes/{id}/reprovar', [SugestaoController::class, 'reprovar']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
