<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/start-sync', [SyncController::class, 'startSync']);
