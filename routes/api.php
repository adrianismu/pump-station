<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PumpHouseController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WeatherController;
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

// CSRF Token endpoint for frontend
Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});

// Notification routes (using web auth middleware for compatibility with session auth)
Route::middleware('auth:web')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('api.notifications.count');
    Route::get('/notifications/recent-breaches', [NotificationController::class, 'recentBreaches'])->name('api.notifications.recent-breaches');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    Route::apiResource('pump-houses', PumpHouseController::class);
});

// Public Weather API routes (for landing page and public access)
Route::get('/weather/public', [WeatherController::class, 'show'])->name('api.weather.public');

// Weather API routes without auth (for both public and admin use)
Route::get('/weather', [WeatherController::class, 'show'])->name('api.weather');

// Protected Weather API routes (using web auth for specific pump house data)
Route::middleware('auth:web')->group(function () {
    Route::get('/weather/pump-house/{pumpHouse}', [WeatherController::class, 'pumpHouse'])->name('api.weather.pump-house');
});
// Threshold settings API (using web auth for compatibility)
Route::middleware('auth:web')->group(function () {
    Route::get('/threshold-settings', function () {
        $thresholds = \App\Models\ThresholdSetting::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();
        return response()->json(['data' => $thresholds]);
    });
    
    Route::get('/pump-house-thresholds/{pumpHouse}', function ($pumpHouseId) {
        $thresholds = \App\Models\PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)
            ->where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();
        return response()->json(['thresholds' => $thresholds]);
    });
});

