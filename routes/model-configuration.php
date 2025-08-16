<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\ModelConfiguration\Http\Controllers\ConfigurationController;

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

// User Devices routes

Route::apiResource('configurations', ConfigurationController::class);
