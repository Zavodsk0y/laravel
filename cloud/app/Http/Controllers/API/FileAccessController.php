<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\FileAccessService;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class FileAccessController extends Controller
{

    protected $fileAccessService;

    public function __construct(FileAccessService $fileAccessService)
    {
        $this->fileAccessService = $fileAccessService;
    }

    public function addAccessToFile($fileId, Request $request): JsonResponse
    {
        $email = $request->input('email');
        $userAccess = User::where('email', $email)->first();

        $file = File::where('file_id', $fileId)->first();

        if (!$userAccess) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'User with this email not found'
            ]);
        }

        $response = $this->fileAccessService->addAccessToFile($file, $userAccess);

        if (array_key_exists('data', $response)) {
            return response()->json($response['data'], $response['code']);
        } else {
            return response()->json($response, $response['code']);
        }
    }

    public function deleteAccessToFile($fileId, Request $request): JsonResponse
    {
        $email = $request->input('email');
        $file = File::where('file_id', $fileId)->first();
        $response = $this->fileAccessService->deleteAccessToFile($file, $email);
        if (array_key_exists('accesses', $response)) {
            return response()->json($response['accesses'], $response['code']);
        } else {
            return response()->json($response, $response['code']);
        }
    }


}
