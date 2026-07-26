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

// Since auth is not yet implemented, we expose them directly or you can wrap them in a mock auth middleware later.
Route::prefix('jabatan')->group(function () {
    Route::post('/list', [JabatanController::class, 'list']);
    Route::post('/detail', [JabatanController::class, 'detail']);
    Route::post('/create', [JabatanController::class, 'create']);
    Route::post('/update', [JabatanController::class, 'update']);
    Route::post('/delete', [JabatanController::class, 'delete']);
});

Route::prefix('unit-kerja')->group(function () {
    Route::post('/list', [UnitKerjaController::class, 'list']);
    Route::post('/detail', [UnitKerjaController::class, 'detail']);
    Route::post('/create', [UnitKerjaController::class, 'create']);
    Route::post('/update', [UnitKerjaController::class, 'update']);
    Route::post('/delete', [UnitKerjaController::class, 'delete']);
});

Route::prefix('bagian-seksi')->group(function () {
    Route::post('/list', [BagianSeksiController::class, 'list']);
    Route::post('/detail', [BagianSeksiController::class, 'detail']);
    Route::post('/create', [BagianSeksiController::class, 'create']);
    Route::post('/update', [BagianSeksiController::class, 'update']);
    Route::post('/delete', [BagianSeksiController::class, 'delete']);
});
