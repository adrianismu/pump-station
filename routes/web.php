<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\ReportsController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\WaterLevelController;
use App\Http\Controllers\Admin\ThresholdSettingController;
use App\Http\Controllers\Admin\PumpHouseThresholdController;
use App\Http\Controllers\Admin\UserPumpHouseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController as DashboardControllerAlias;
// use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes untuk masyarakat
Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('public.landing');
Route::get('/laporan', [App\Http\Controllers\PublicController::class, 'reports'])->name('public.reports');
Route::post('/laporan/submit', [App\Http\Controllers\PublicController::class, 'submitReport'])->name('public.submit-report');
Route::get('/laporan/sukses', [App\Http\Controllers\PublicController::class, 'reportSuccess'])->name('public.report-success');
Route::get('/edukasi', [App\Http\Controllers\PublicController::class, 'education'])->name('public.education');
Route::get('/edukasi/{id}', [App\Http\Controllers\PublicController::class, 'educationDetail'])->name('public.education.detail');
Route::get('/peta', [App\Http\Controllers\PublicController::class, 'map'])->name('public.map');

// Public API endpoints (no authentication required)
Route::get('/api/public-alerts', [App\Http\Controllers\PublicController::class, 'getActiveAlerts'])->name('api.public-alerts');

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // General dashboard route that redirects based on role
    Route::get('/dashboard', [DashboardControllerAlias::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::middleware(['admin.only'])->group(function () {
        Route::get('/admin/dashboard', [DashboardControllerAlias::class, 'adminDashboard'])->name('admin.dashboard');
        // Route::resource('users', UserController::class);
    });

    // Petugas routes
    Route::middleware(['petugas.only'])->group(function () {
        Route::get('/petugas/dashboard', [DashboardControllerAlias::class, 'petugasDashboard'])->name('petugas.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes - Hanya untuk admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Routes yang bisa diakses admin dan petugas (dengan pembatasan)
    Route::middleware(['pump.access'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Database Map and Detail - Dapat diakses admin dan petugas sesuai akses
        Route::get('/map', [MapController::class, 'index'])->name('map');
        Route::get('/database/create', [DatabaseController::class, 'create'])->name('database.create');
        Route::get('/database/{id}', [DatabaseController::class, 'show'])->name('database.show')->middleware('pump.access:read');
        Route::get('/database/{pumpHouse}/edit', [DatabaseController::class, 'edit'])->name('database.edit')->middleware('pump.access:write');
        Route::put('/database/{pumpHouse}', [DatabaseController::class, 'update'])->name('database.update')->middleware('pump.access:write');
        Route::put('/database/{pumpHouse}/toggle-status', [DatabaseController::class, 'toggleStatus'])->name('database.toggle-status')->middleware('pump.access:write');
        Route::put('/database/{pumpHouse}/pump-status', [\App\Http\Controllers\Admin\PumpHouseController::class, 'updatePumpStatus'])->name('database.update-pump-status')->middleware('pump.access:write');
        
        // Water Level Management Routes - Petugas bisa akses sesuai pump house mereka
        Route::resource('water-level', WaterLevelController::class);
        Route::get('/water-level/{pumpHouse}/history', [WaterLevelController::class, 'history'])->name('water-level.history')->middleware('pump.access:read');
        Route::get('/water-level/{pumpHouse}/chart-data', [WaterLevelController::class, 'getChartData'])->name('water-level.chart-data')->middleware('pump.access:read');
        
        // Pump House Threshold Management - Petugas bisa akses sesuai pump house mereka
        Route::get('/pump-house-thresholds', [PumpHouseThresholdController::class, 'index'])->name('pump-house-thresholds.index');
        Route::get('/pump-house-thresholds/{pumpHouse}', [PumpHouseThresholdController::class, 'show'])->name('pump-house-thresholds.show')->middleware('pump.access:read');
        Route::get('/pump-house-thresholds/{pumpHouse}/edit', [PumpHouseThresholdController::class, 'edit'])->name('pump-house-thresholds.edit')->middleware('pump.access:write');
        Route::put('/pump-house-thresholds/{pumpHouse}', [PumpHouseThresholdController::class, 'update'])->name('pump-house-thresholds.update')->middleware('pump.access:write');
        Route::post('/pump-house-thresholds/{pumpHouse}/copy-default', [PumpHouseThresholdController::class, 'copyFromDefault'])->name('pump-house-thresholds.copy-default')->middleware('pump.access:write');
        Route::post('/pump-house-thresholds/{pumpHouse}/reset-default', [PumpHouseThresholdController::class, 'resetToDefault'])->name('pump-house-thresholds.reset-default')->middleware('pump.access:write');
        Route::delete('/pump-house-thresholds/{pumpHouse}/thresholds/{threshold}', [PumpHouseThresholdController::class, 'destroy'])->name('pump-house-thresholds.destroy')->middleware('pump.access:write');
        
        // Notifications - Petugas bisa lihat notifikasi sesuai pump house mereka
        Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications');
        Route::get('/notifications/api', [NotificationsController::class, 'apiIndex'])->name('notifications.api');
        Route::get('/notifications/count', [NotificationsController::class, 'apiCount'])->name('notifications.count');
        Route::get('/notifications/{alert}', [NotificationsController::class, 'show'])->name('notifications.show');
        
        // Reports - Petugas bisa lihat laporan sesuai pump house mereka
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/reports/{report}', [ReportsController::class, 'show'])->name('reports.show');
        Route::put('/reports/{report}', [ReportsController::class, 'update'])->name('reports.update');
        Route::post('/reports/{report}/responses', [ReportsController::class, 'addResponse'])->name('reports.responses.store');
    });
    
    // Routes khusus admin saja
    Route::middleware(['admin.only'])->group(function () {
        // API test route dan admin actions
        Route::get('/notifications/test', function() {
            return response()->json(['message' => 'Test route works', 'unread_count' => 0]);
        })->name('notifications.test');
        Route::post('/notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');
        
        // Routes dengan parameter harus di bawah - Admin only actions
        Route::post('/notifications', [NotificationsController::class, 'store'])->name('notifications.store');
        Route::post('/notifications/{alert}/actions', [NotificationsController::class, 'addAction'])->name('notifications.actions.store');
        Route::put('/notifications/{alert}', [NotificationsController::class, 'update'])->name('notifications.update');
        Route::post('/notifications/{alert}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.read');
        
        // Database Management - Admin only
        Route::get('/database', [DatabaseController::class, 'index'])->name('database');
        Route::post('/database', [DatabaseController::class, 'store'])->name('database.store');
        Route::delete('/database/{pumpHouse}', [DatabaseController::class, 'destroy'])->name('database.destroy');
        
        // Reports Management - Admin only actions
        Route::delete('/reports/{report}', [ReportsController::class, 'destroy'])->name('reports.destroy');
        
        // Education Management - Admin only
        Route::get('/education', [EducationController::class, 'index'])->name('education');
        Route::get('/education/create', [EducationController::class, 'create'])->name('education.create');
        Route::post('/education', [EducationController::class, 'store'])->name('education.store');
        Route::get('/education/{educationContent}', [EducationController::class, 'show'])->name('education.show');
        Route::get('/education/{educationContent}/edit', [EducationController::class, 'edit'])->name('education.edit');
        Route::put('/education/{educationContent}', [EducationController::class, 'update'])->name('education.update');
        Route::delete('/education/{educationContent}', [EducationController::class, 'destroy'])->name('education.destroy');
        
        // Threshold Settings Management - Admin only
        Route::resource('threshold-settings', ThresholdSettingController::class);
        
        // User Pump House Access Management - Admin only
        Route::get('/user-pump-house', [UserPumpHouseController::class, 'index'])->name('user-pump-house.index');
        Route::get('/user-pump-house/{user}', [UserPumpHouseController::class, 'show'])->name('user-pump-house.show');
        Route::post('/user-pump-house/{user}/assign', [UserPumpHouseController::class, 'assignPumpHouse'])->name('user-pump-house.assign');
        Route::put('/user-pump-house/{user}/{pumpHouse}', [UserPumpHouseController::class, 'updateAccess'])->name('user-pump-house.update');
        Route::delete('/user-pump-house/{user}/{pumpHouse}', [UserPumpHouseController::class, 'revokeAccess'])->name('user-pump-house.revoke');
        Route::post('/user-pump-house/bulk-assign', [UserPumpHouseController::class, 'bulkAssign'])->name('user-pump-house.bulk-assign');
        Route::post('/user-pump-house/copy-access', [UserPumpHouseController::class, 'copyAccess'])->name('user-pump-house.copy-access');
    });
});

require __DIR__.'/auth.php';

// Health check endpoint for Railway monitoring
Route::get('/health', function () {
    $recentAlerts = \App\Models\Alert::where('type', 'weather_forecast')
        ->where('created_at', '>=', now()->subHours(2))
        ->count();
        
    $recentNotifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('created_at', '>=', now()->subHours(2))
        ->count();
        
    $lastSchedulerRun = \Illuminate\Support\Facades\DB::table('job_batches')
        ->latest('created_at')
        ->value('created_at');
    
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment(),
        'queue_connection' => config('queue.default'),
        'database' => [
            'notifications_total' => \Illuminate\Support\Facades\DB::table('notifications')->count(),
            'notifications_recent' => $recentNotifications,
            'alerts_total' => \App\Models\Alert::count(),
            'alerts_recent' => $recentAlerts,
            'failed_jobs' => \Illuminate\Support\Facades\Schema::hasTable('failed_jobs') 
                ? \Illuminate\Support\Facades\DB::table('failed_jobs')->count() 
                : 0,
        ],
        'users' => [
            'total' => \App\Models\User::count(),
            'admins' => \App\Models\User::role('admin')->count(),
            'petugas' => \App\Models\User::role('petugas')->count(),
            'roles_seeded' => \Spatie\Permission\Models\Role::count() > 0,
        ],
        'pump_houses' => [
            'total' => \App\Models\PumpHouse::count(),
            'with_coordinates' => \App\Models\PumpHouse::whereNotNull('lat')->whereNotNull('lng')->count(),
        ],
        'scheduler' => [
            'last_run' => $lastSchedulerRun,
            'weather_alerts_enabled' => true,
        ],
        'system' => [
            'memory_usage' => number_format(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'peak_memory' => number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]
    ]);
});
