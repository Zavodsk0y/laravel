<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    public function uploadFiles(Request $request): array|JsonResponse
    {
        $files = $request->file('files');
        $user = auth()->user();

        if ($files) {
            $jsonResps = [];
            foreach ($files as $uploadFile) {
                $rules = array('file' => 'required|file|mimes:doc,pdf,docx,zip,jpeg,jpg,png|max:2048', 'files.*' => 'required');
                $validator = Validator::make(array('file' => $uploadFile), $rules);

                if ($validator->fails()) {
                    $response = [
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'name' => $uploadFile->getClientOriginalName()
                    ];
                } else {
                    $fileId = Str::random(10);

                    $uploadFileName = $uploadFile->getClientOriginalName();
                    $newFileName = $uploadFileName;
                    $counter = 1;
                    while (Storage::exists("uploads/$newFileName")) {
                        $pathInfo = pathinfo($uploadFileName);
                        $newFileName = $pathInfo['filename'] . " ($counter)." . $pathInfo['extension'];
                        $counter++;
                    }


                    $uploadFile->storeAs('uploads/', $newFileName);

                    $file = new File([
                        'user_id' => $user->id,
                        'file_id' => $fileId,
                        'name' => $newFileName, // Имя файла
                    ]);

                    $file->save();

                    $response = [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Success',
                        'name' => $newFileName,
                        'url' => url('api/files/' . $fileId),
                        'file_id' => $fileId,
                    ];


                }
                $jsonResps[] = $response;
            }
        } else {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => [
                    'files' => ['field files can not be blank'],
                ],
            ], 422);
        }

        return $jsonResps;
    }

    public function deleteFile($file_id): JsonResponse
    {
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Not found',
            ], 404);
        }

        if ($file->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Forbidden for you',
            ], 403);
        }

        Storage::delete('uploads/' . $file->name);

        $file->delete();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'File deleted',
        ], 200);
    }

    public function updateFileName($file_id, Request $request): JsonResponse
    {
        $user = auth()->user();
        $newFileName = $request->input('name');

        $file = File::where('file_id', $file_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'File not found',
            ], 404);
        }

        if ($file->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Forbidden for you',
            ], 403);
        }

        $existingFile = File::where('name', $newFileName)
            ->where('user_id', $user->id)
            ->where('id', '<>', $file->id)
            ->first();

        if ($existingFile) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'Name already exists',
            ], 422);
        }

        try {
            $request->validate([
                'name' => 'required'
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return response()->json([
                'success' => false,
                'code' => 422,
                'messsage' => $errors,
            ], 422);
        }

        $pathInfo = pathinfo($file->name);
        $newFileName = $request->input('name') . '.' . $pathInfo['extension'];

        Storage::move("uploads/$file->name", "uploads/$newFileName");

        $file->name = $newFileName;
        $file->save();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Renamed',
        ], 200);
    }

    public function downloadFile($file_id)
    {
        $user = auth()->user();

        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'File not found',
            ], 404);
        }

        if ($file->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Access denied',
            ], 403);
        }

        $filePath = storage_path("app/uploads/$file->name");

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'File not found on server',
            ], 404);
        }

        return response()->download($filePath);
    }

}
