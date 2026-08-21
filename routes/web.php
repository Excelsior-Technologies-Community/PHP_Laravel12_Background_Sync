<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;

Route::get('/', fn() => view('welcome'));

Route::get('/sync-dashboard', [SyncController::class, 'dashboard'])->name('sync.dashboard');
Route::post('/start-sync', [SyncController::class, 'startSync'])->name('sync.start');
Route::delete('/sync-history/{id}', [SyncController::class, 'destroy'])->name('sync.destroy');
Route::post('/sync-bulk-delete', [SyncController::class, 'bulkDelete'])->name('sync.bulkDelete');
Route::post('/sync-pause/{id}', [SyncController::class, 'pause'])->name('sync.pause');
Route::post('/sync-resume/{id}', [SyncController::class, 'resume'])->name('sync.resume');
Route::post('/sync-cancel/{id}', [SyncController::class, 'cancel'])->name('sync.cancel');
Route::post('/sync-retry/{id}', [SyncController::class, 'retry'])->name('sync.retry');
Route::get('/sync-progress/{id}', [SyncController::class, 'progress'])->name('sync.progress');
Route::get('/sync-export-csv', [SyncController::class, 'exportCsv'])->name('sync.exportCsv');
Route::get('/sync-log/{id}', [SyncController::class, 'showLog'])->name('sync.log');
