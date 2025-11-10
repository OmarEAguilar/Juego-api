<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameSessionController;

// Ruta de prueba para aislar 404
Route::get('/ping', fn () => response()->json(['pong' => true]));

// API del juego
Route::post('/sessions', [GameSessionController::class, 'store']);
Route::get('/leaderboard', [GameSessionController::class, 'leaderboard']);
