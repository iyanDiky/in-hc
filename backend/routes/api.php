<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\BagianSeksiController;

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

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);

    // Unit Kerja
    Route::prefix('unit-kerja')->group(function () {
        Route::post('/datatables', [UnitKerjaController::class, 'datatables']);
        Route::post('/list', [UnitKerjaController::class, 'list']);
        Route::post('/detail', [UnitKerjaController::class, 'detail']);
        Route::post('/create', [UnitKerjaController::class, 'create']);
        Route::post('/update', [UnitKerjaController::class, 'update']);
        Route::post('/delete', [UnitKerjaController::class, 'delete']);
    });

    // Jabatan
    Route::prefix('jabatan')->group(function () {
        Route::post('/datatables', [JabatanController::class, 'datatables']);
        Route::post('/list', [JabatanController::class, 'list']);
        Route::post('/detail', [JabatanController::class, 'detail']);
        Route::post('/create', [JabatanController::class, 'create']);
        Route::post('/update', [JabatanController::class, 'update']);
        Route::post('/delete', [JabatanController::class, 'delete']);
    });

    // Bagian Seksi
    Route::prefix('bagian-seksi')->group(function () {
        Route::post('/datatables', [BagianSeksiController::class, 'datatables']);
        Route::post('/list', [BagianSeksiController::class, 'list']);
        Route::post('/detail', [BagianSeksiController::class, 'detail']);
        Route::post('/create', [BagianSeksiController::class, 'create']);
        Route::post('/update', [BagianSeksiController::class, 'update']);
        Route::post('/delete', [BagianSeksiController::class, 'delete']);
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::post('/datatables', [App\Http\Controllers\UserController::class, 'datatables']);
        Route::post('/list', [App\Http\Controllers\UserController::class, 'list']);
        Route::post('/detail', [App\Http\Controllers\UserController::class, 'detail']);
        Route::post('/create', [App\Http\Controllers\UserController::class, 'create']);
        Route::post('/update', [App\Http\Controllers\UserController::class, 'update']);
        Route::post('/delete', [App\Http\Controllers\UserController::class, 'delete']);
        Route::post('/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword']);
    });
    // Surat Masuk
    Route::prefix('surat-masuk')->group(function () {
        Route::post('/datatables', [\App\Http\Controllers\SuratMasukController::class, 'datatables']);
        Route::post('/years', [\App\Http\Controllers\SuratMasukController::class, 'years']);
        Route::post('/list', [\App\Http\Controllers\SuratMasukController::class, 'list']);
        Route::post('/detail', [\App\Http\Controllers\SuratMasukController::class, 'detail']);
        Route::post('/create', [\App\Http\Controllers\SuratMasukController::class, 'create']);
        Route::post('/update', [\App\Http\Controllers\SuratMasukController::class, 'update']);
        Route::post('/delete', [\App\Http\Controllers\SuratMasukController::class, 'delete']);
        
        // Disposisi
        Route::get('/{id}/disposisi', [\App\Http\Controllers\SuratMasukDisposisiController::class, 'index']);
        Route::post('/{id}/disposisi', [\App\Http\Controllers\SuratMasukDisposisiController::class, 'store']);
        Route::delete('/{id}/disposisi/{disposisiId}', [\App\Http\Controllers\SuratMasukDisposisiController::class, 'destroy']);
    });

    // Surat Keluar
    Route::prefix('surat-keluar')->group(function () {
        Route::post('/datatables', [\App\Http\Controllers\SuratKeluarController::class, 'datatables']);
        Route::post('/years', [\App\Http\Controllers\SuratKeluarController::class, 'years']);
        Route::post('/list', [\App\Http\Controllers\SuratKeluarController::class, 'list']);
        Route::post('/detail', [\App\Http\Controllers\SuratKeluarController::class, 'detail']);
        Route::post('/next-number', [\App\Http\Controllers\SuratKeluarController::class, 'nextNumber']);
        Route::post('/create', [\App\Http\Controllers\SuratKeluarController::class, 'create']);
        Route::post('/update', [\App\Http\Controllers\SuratKeluarController::class, 'update']);
        Route::post('/delete', [\App\Http\Controllers\SuratKeluarController::class, 'delete']);
    });

    // Dashboard Analytics
    Route::prefix('dashboard')->group(function () {
        Route::post('/summary', [\App\Http\Controllers\DashboardController::class, 'summary']);
        Route::post('/chart-monthly', [\App\Http\Controllers\DashboardController::class, 'chartMonthly']);
        Route::post('/chart-weekly', [\App\Http\Controllers\DashboardController::class, 'chartWeekly']);
        Route::post('/chart-distribution', [\App\Http\Controllers\DashboardController::class, 'chartDistribution']);
        Route::post('/recent-activities', [\App\Http\Controllers\DashboardController::class, 'recentActivities']);
        Route::post('/recent-surat', [\App\Http\Controllers\DashboardController::class, 'recentSurat']);
    });

    // Pengelolaan Data Pelamar (Lamaran)
    Route::prefix('lamaran')->group(function () {
        Route::post('/datatables', [\App\Http\Controllers\LamaranController::class, 'datatables']);
        Route::post('/list', [\App\Http\Controllers\LamaranController::class, 'list']);
        Route::post('/detail', [\App\Http\Controllers\LamaranController::class, 'detail']);
        Route::post('/next-number', [\App\Http\Controllers\LamaranController::class, 'nextNumber']);
        Route::post('/create', [\App\Http\Controllers\LamaranController::class, 'create']);
        Route::post('/update', [\App\Http\Controllers\LamaranController::class, 'update']);
        Route::post('/delete', [\App\Http\Controllers\LamaranController::class, 'delete']);
    });
});

