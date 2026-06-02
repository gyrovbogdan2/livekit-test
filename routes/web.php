<?php

use App\Http\Controllers\VideoCallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/call', [VideoCallController::class, 'index'])->name('call.index');
Route::get('/call/token', [VideoCallController::class, 'token'])->name('call.token');
