<?php

use App\Http\Controllers\API\ApiAuthController;
use App\Http\Controllers\API\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    Log::info('User: ' . $request->user());
    return $request->user();
});

Route::post('/registration', [ApiAuthController::class, 'register']);

Route::post('/authorization', [ApiAuthController::class, 'authorization']);

Route::get('/logout', [ApiAuthController::class, 'logout']);

Route::post('/files', [FileController::class, 'uploadFiles']);
