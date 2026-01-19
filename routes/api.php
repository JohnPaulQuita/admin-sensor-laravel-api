<?php

use App\Http\Controllers\CubicleController;
use Illuminate\Support\Facades\Route;

Route::post('/update-status', [CubicleController::class, 'updateStatus']);
Route::get('/get-status/{id}', [CubicleController::class, 'getStatus']);
Route::get('/all-status', [CubicleController::class, 'getAllStatus']);