<?php

use Illuminate\Support\Facades\Route;

// Welcome route
Route::get('/', function () {
    return view('welcome');
});

// Player ticket route - serves the Vue SPA for ticket playing
Route::get('/t/{code}', function (string $code) {
    // In a real implementation, you'd return a Vue SPA view
    // For now, return a simple view that can bootstrap the Vue app
    return view('ticket', compact('code'));
})->where('code', '[A-Z0-9]{8,10}');

// Admin routes - all serve the same SPA view
Route::prefix('admin')->group(function () {
    // All admin routes serve the same SPA
    Route::get('/{any?}', function () {
        return view('admin.dashboard');
    })->where('any', '.*');
});
