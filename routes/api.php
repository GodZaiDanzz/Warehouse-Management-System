<?php

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

/*
|--------------------------------------------------------------------------
| QC / QA Integration API Routes
|--------------------------------------------------------------------------
|
| Endpoint API khusus untuk integrasi website QC/QA menampilkan data
| stok barang gudang dan transaksi material masuk.
|
*/
Route::prefix('qc')->group(function () {
    // Ringkasan & Dashboard Metrics
    Route::get('/summary', [\App\Http\Controllers\Api\QcApiController::class, 'summary']);

    // Data Stok Barang
    Route::get('/stocks', [\App\Http\Controllers\Api\QcApiController::class, 'stocks']);
    Route::get('/stocks/{id}', [\App\Http\Controllers\Api\QcApiController::class, 'stockDetail']);

    // Alias URL Barang
    Route::get('/items', [\App\Http\Controllers\Api\QcApiController::class, 'stocks']);
    Route::get('/items/{id}', [\App\Http\Controllers\Api\QcApiController::class, 'stockDetail']);

    // Data Material Masuk (Inbound Receipts)
    Route::get('/material-ins', [\App\Http\Controllers\Api\QcApiController::class, 'materialIns']);
    Route::get('/material-ins/{id}', [\App\Http\Controllers\Api\QcApiController::class, 'materialInDetail']);
});

