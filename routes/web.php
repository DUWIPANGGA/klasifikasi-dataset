<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\DuplicateController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExportDownloadController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MissingController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/images', [ImageController::class, 'index'])->name('images.index');
    Route::get('/images/{image}', [ImageController::class, 'show'])->name('images.show');

    Route::get('/import', [ImportController::class, 'index'])->name('import');
    Route::get('/export', [ExportController::class, 'index'])->name('export');
    Route::get('/export/download', ExportDownloadController::class)->name('export.download');

    Route::get('/duplicates', [DuplicateController::class, 'index'])->name('duplicates');
    Route::get('/missing', [MissingController::class, 'index'])->name('missing');

    Route::get('/datasets', [DatasetController::class, 'index'])->name('datasets');

    Route::get('/image-proxy', ImageProxyController::class)->name('image-proxy');
});
