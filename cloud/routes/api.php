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
    Route::post("/files", [FileController::class, 'uploadFiles']);
    Route::delete("/files/{file_id}", [FileController::class, 'deleteFile']);
    Route::patch("files/{file_id}", [FileController::class, 'updateFileName']);
    Route::get('/files/disk', [FileController::class, 'getUserFiles']);
    Route::get('/files/shared', [FileController::class, 'getAccessedFiles']);
    Route::get("/files/{file_id}", [FileController::class, 'downloadFile']);
    Route::post("/files/{file_id}/accesses", [FileAccessController::class, 'addAccessToFile']);
    Route::delete('/files/{file_id}/accesses', [FileAccessController::class, 'deleteAccessToFile']);
});





