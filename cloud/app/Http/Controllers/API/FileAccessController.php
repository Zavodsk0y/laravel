<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\FileAccessService;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class FileAccessController extends Controller
{
    public function __construct(protected FileAccessService $fileAccessService)
    {
    }

    public function addAccessToFile($fileId, Request $request): JsonResponse
    {
        $email = $request->input('email');

        $file = File::where('file_id', $fileId)->first();

        $this->authorize('fileAccess', $file);

        $response = $this->fileAccessService->addAccessToFile($file, $email);

        if (array_key_exists('data', $response)) {
            return response()->json($response['data'], $response['code']);
        }
        return response()->json($response, $response['code']);
    }

    public function deleteAccessToFile($fileId, Request $request): JsonResponse
    {
        $email = $request->input('email');

        $file = File::where('file_id', $fileId)->first();

        $this->authorize('fileAccess', $file);

        $response = $this->fileAccessService->deleteAccessToFile($file, $email);
        if (array_key_exists('accesses', $response)) {
            return response()->json($response['accesses'], $response['code']);
        }
        return response()->json($response, $response['code']);
    }


}
