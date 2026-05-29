<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Start Background Sync
 */
Route::get('/start-sync', [SyncController::class, 'startSync']);


/**
 * Sync Dashboard
 */
Route::get('/sync-dashboard', [SyncController::class, 'dashboard']);