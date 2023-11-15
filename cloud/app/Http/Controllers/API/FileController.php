<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilesUploadRequest;
use App\Http\Services\FileService;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function uploadFiles(Request $request): JsonResponse
    {
        $files = $request->file('files');
        $user = auth()->user();

        $responses = [];

        foreach ($files as $uploadFile) {
            $response = $this->fileService->processFile($uploadFile, $user);
            $responses[] = $response;
        }

        return response()->json($responses);
    }


    public function deleteFile($fileId): JsonResponse
    {
        $response = $this->fileService->deleteFile($fileId);

        return response()->json($response, $response['code']);
    }

    public function updateFileName($fileId, FilesUploadRequest $request): JsonResponse
    {
        $user = auth()->user();
        $file = File::where('file_id', $fileId)->first();
        $newFileName = $request->input('name') . '.' . pathinfo($file->name)['extension'];


        $response = $this->fileService->updateFileName($newFileName, $fileId, $user);

        return response()->json($response, $response['code']);
    }

    public function downloadFile($fileId): BinaryFileResponse|JsonResponse
    {
        $file = File::where('file_id', $fileId)->first();

        $response = $this->fileService->downloadFile($fileId, $file);

        return response()->download($response);
    }

    public function getUserFiles(): JsonResponse
    {
        $user = auth()->user();

        $files = File::where('user_id', $user->id)->with(['accesses.user'])->get();

        $response = $this->fileService->getUserFiles($files);

        return response()->json($response);
    }

    public function getAccessedFiles(): JsonResponse
    {
        $user = auth()->user();

        $files = File::whereHas('accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('type', 'co-author');
        })->get();

        $response = $this->fileService->getAccessedFiles($files);

        return response()->json($response);
    }

}
