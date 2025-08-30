<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PlayController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PrizeTierController;
use App\Http\Controllers\Admin\GenerationBatchController;

// Public API routes for players
Route::prefix('tickets')->group(function () {
    Route::get('/{code}', [TicketController::class, 'show']);
});

Route::prefix('plays')->group(function () {
    Route::patch('/{id}/scratch', [PlayController::class, 'scratch']);
    Route::get('/{id}/verify', [PlayController::class, 'verify']);
});

// Admin API routes
Route::prefix('admin')->group(function () {
    // Dashboard
    Route::get('/summary', [DashboardController::class, 'summary']);
    
    // Prize Tiers
    Route::get('/prize-tiers', [PrizeTierController::class, 'index']);
    Route::post('/prize-tiers', [PrizeTierController::class, 'store']);
    Route::delete('/prize-tiers/{id}', [PrizeTierController::class, 'destroy']);
    
    // Generation & Batches
    Route::post('/generate', [GenerationBatchController::class, 'generate']);
    Route::get('/batches', [GenerationBatchController::class, 'index']);
    Route::get('/batches/{id}', [GenerationBatchController::class, 'show']);
    Route::get('/batches/{id}/tickets', [GenerationBatchController::class, 'tickets']);
    Route::get('/batches/{id}/export', [GenerationBatchController::class, 'export']);
});
