<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BeritaController;
use App\Http\Controllers\API\KategoriBeritaController;
use App\Http\Controllers\API\LayananController;
use App\Http\Controllers\API\JenisLayananController;
use App\Http\Controllers\API\SaranController;
use App\Http\Controllers\API\NotifikasiController;


// Test route untuk memastikan API berjalan
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// Auth routes tanpa middleware dulu untuk testing
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('berita', [BeritaController::class, 'index']);
    Route::get('berita/{id}', [BeritaController::class, 'show']);
    Route::get('home', [BeritaController::class, 'homeData']);
});

// Alternative route structure (jika yang atas tidak work)
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::get('berita', [BeritaController::class, 'index']);
Route::get('berita/{id}', [BeritaController::class, 'show']);
Route::get('home', [BeritaController::class, 'homeData']);
Route::get('kategori-berita', [KategoriBeritaController::class, 'index']);
Route::get('kategori-berita/{id}', [KategoriBeritaController::class, 'show']);
Route::get('kategori-berita/{id}/berita', [KategoriBeritaController::class, 'getBerita']);

Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::post('kategori-berita', [KategoriBeritaController::class, 'store']);
    Route::put('kategori-berita/{id}', [KategoriBeritaController::class, 'update']);
    Route::delete('kategori-berita/{id}', [KategoriBeritaController::class, 'destroy']);
});

Route::get('layanan', [LayananController::class, 'index']);
Route::get('layanan/{id}', [LayananController::class, 'show']);

Route::get('jenis-layanan', [JenisLayananController::class, 'index']);
Route::get('jenis-layanan/{id}', [JenisLayananController::class, 'show']);

Route::middleware('auth:api')->prefix('admin')->group(function () {
    // Layanan management
    Route::post('layanan', [LayananController::class, 'store']);
    Route::put('layanan/{id}', [LayananController::class, 'update']);
    Route::delete('layanan/{id}', [LayananController::class, 'destroy']);
    
    // Jenis Layanan management
    Route::post('jenis-layanan', [JenisLayananController::class, 'store']);
    Route::put('jenis-layanan/{id}', [JenisLayananController::class, 'update']);
    Route::delete('jenis-layanan/{id}', [JenisLayananController::class, 'destroy']);
});

Route::get('saran', [SaranController::class, 'index']); // Public saran list
Route::get('saran/{id}', [SaranController::class, 'show']); // Single saran detail
Route::post('saran', [SaranController::class, 'store']); // Submit saran (public/guest)

Route::get('saran', [SaranController::class, 'index']); // Public saran list
Route::get('saran/{id}', [SaranController::class, 'show']); // Single saran detail
Route::post('saran', [SaranController::class, 'store']); // Submit saran (public/guest)

Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('saran/statistics', [SaranController::class, 'statistics']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('notifikasi', [NotifikasiController::class, 'index']);
    Route::get('notifikasi/{id}', [NotifikasiController::class, 'show']);
    Route::put('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::put('notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead']);
    Route::get('notifikasi-count', [NotifikasiController::class, 'getUnreadCount']);
    Route::delete('notifikasi/{id}', [NotifikasiController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('notifikasi', [NotifikasiController::class, 'adminIndex']);
    Route::post('notifikasi', [NotifikasiController::class, 'store']);
    Route::post('notifikasi/broadcast', [NotifikasiController::class, 'broadcast']);
    Route::delete('notifikasi/{id}', [NotifikasiController::class, 'adminDestroy']);
    Route::get('notifikasi/statistics', [NotifikasiController::class, 'statistics']);
});