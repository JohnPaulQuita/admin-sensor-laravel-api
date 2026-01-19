<?php

use App\Http\Controllers\CubicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CubicleController::class, 'dashboard']);