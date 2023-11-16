<?php

use App\Http\Controllers\API\ApiAuthController;
use App\Http\Controllers\API\FileAccessController;
use App\Http\Controllers\API\FileController;
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

Route::post('/registration', [ApiAuthController::class, 'register']);
Route::post('/authorization', [ApiAuthController::class, 'authorization']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/files/disk', [FileController::class, 'index']);
    Route::get('/files/shared', [FileController::class, 'getAccessedFiles']);
    Route::apiResource('/files', FileController::class)->except('index');
    Route::post("/files/{file_id}/accesses", [FileAccessController::class, 'addAccessToFile']);
    Route::delete('/files/{file_id}/accesses', [FileAccessController::class, 'deleteAccessToFile']);
});




