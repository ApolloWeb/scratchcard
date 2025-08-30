<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PrizeTierController;
use App\Http\Controllers\Admin\GenerationBatchController;
use App\Http\Controllers\Admin\PlaySessionController;
use App\Http\Controllers\Admin\GameSettingController;

Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function(){
        Route::get('/', function(){ return view('admin.index'); })->name('index');

        Route::apiResource('prize-tiers', PrizeTierController::class)->parameters(['prize-tiers' => 'prizeTier']);
        Route::apiResource('generation-batches', GenerationBatchController::class)->parameters(['generation-batches' => 'generationBatch']);
        Route::get('play-sessions', [PlaySessionController::class, 'index'])->name('play-sessions.index');
        Route::get('play-sessions/{playSession}', [PlaySessionController::class, 'show'])->name('play-sessions.show');
        Route::patch('play-sessions/{playSession}', [PlaySessionController::class, 'update'])->name('play-sessions.update');
    });
});


