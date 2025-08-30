<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PrizeTierController;
use App\Http\Controllers\Admin\GenerationBatchController;
use App\Http\Controllers\Admin\PlaySessionController;
use App\Http\Controllers\Admin\GameSettingController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/', function () {
            return view('admin.index');
        })->name('admin.dashboard');

        Route::resource('users', AdminUserController::class)->parameters(['users' => 'adminUser']);
        Route::resource('campaigns', CampaignController::class)->parameters(['campaigns' => 'campaign']);
        Route::resource('prize-tiers', PrizeTierController::class)->parameters(['prize-tiers' => 'prizeTier']);
        Route::resource('batches', GenerationBatchController::class)->parameters(['batches' => 'generationBatch']);
        Route::resource('play-sessions', PlaySessionController::class)->parameters(['play-sessions' => 'playSession']);
        Route::resource('game-settings', GameSettingController::class)->parameters(['game-settings' => 'gameSetting']);
    });
});
