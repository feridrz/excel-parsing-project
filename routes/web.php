<?php

use App\Http\Controllers\ExcelImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.basic')->group(function () {
    Route::get('/', [ExcelImportController::class, 'showUploadForm']);
    Route::post('/excel/upload', [ExcelImportController::class, 'upload']);
    Route::get('/excel/progress', [ExcelImportController::class, 'progress']);
    Route::get('/rows', [ExcelImportController::class, 'showData']);
});


