<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\FileService;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function __construct(protected FileService $fileService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $files = $request->file('files');

        $responses = [];

        foreach ($files as $uploadFile) {
            $response = $this->fileService->processFile($uploadFile);
            $responses[] = $response;
        }

        return response()->json($responses);
    }


    public function destroy($fileId): JsonResponse
    {
        $file = File::where('file_id', $fileId)->first();

        $this->authorize('fileAccess', $file);

        $response = $this->fileService->deleteFile($file);

        return response()->json($response);
    }

    public function update($fileId, Request $request): JsonResponse
    {
        $newFileName = $request->input('name');

        $file = File::where('file_id', $fileId)->first();

        $this->authorize('fileAccess', $file);

        $response = $this->fileService->updateFileName($newFileName, $file);

        return response()->json($response, $response['code']);
    }

    public function show($fileId): BinaryFileResponse|JsonResponse
    {
        $file = File::where('file_id', $fileId)->first();

        $this->authorize('download', $file);

        $response = $this->fileService->downloadFile($file);

        return response()->download($response);
    }

    public function index(): JsonResponse
    {
        $files = File::where('user_id', auth()->user()->id)->with(['accesses.user'])->get();

        $response = $this->fileService->getUserFiles($files);

        return response()->json($response);
    }

    public function getAccessedFiles(): JsonResponse
    {
        $response = $this->fileService->getAccessedFiles();

        return response()->json($response);
    }

}
