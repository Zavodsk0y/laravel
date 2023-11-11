<?php

use App\Http\Controllers\API\ApiAuthController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\FileAccessController;
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

Route::middleware('auth:sanctum')->post('/files', [FileController::class, 'uploadFiles']);

Route::middleware('auth:sanctum')->delete('/files/{file_id}', [FileController::class, 'deleteFile']);

Route::middleware('auth:sanctum')->patch('/files/{file_id}', [FileController::class, 'updateFileName']);

Route::middleware('auth:sanctum')->get('/files/{file_id}', [FileController::class, 'downloadFile']);

Route::middleware('auth:sanctum')->post('files/{file_id}/accesses', [FileAccessController::class, 'addAccessToFile']);





