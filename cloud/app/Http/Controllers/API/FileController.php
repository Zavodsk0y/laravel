<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilesUploadRequest;
use App\Http\Services\FileService;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
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
        $response = $this->fileService->deleteFile($fileId);

        return response()->json($response);
    }

    public function update($fileId, Request $request): JsonResponse
    {
        $newFileName = $request->input('name');

        $response = $this->fileService->updateFileName($newFileName, $fileId);

        return response()->json($response, $response['code']);
    }

    public function show($fileId): BinaryFileResponse|JsonResponse
    {
        $file = File::where('file_id', $fileId)->first();

        $response = $this->fileService->downloadFile($fileId, $file);

        return response()->download($response);
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();

        $files = File::where('user_id', $user->id)->with(['accesses.user'])->get();

        $response = $this->fileService->getUserFiles($files);

        return response()->json($response);
    }

    public function getAccessedFiles(): JsonResponse
    {
        $response = $this->fileService->getAccessedFiles();

        return response()->json($response);
    }

}
