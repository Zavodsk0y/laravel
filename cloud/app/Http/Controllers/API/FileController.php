<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{

    public function uploadFiles(Request $request): JsonResponse
    {
        $files = $request->file('files');
        $user = auth()->user();

        if ($files) {
            $responses = [];
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
                    $uploadFileName = $uploadFile->getClientOriginalName();

                    $existingFile = File::where('name', $uploadFileName)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existingFile) {
                        $counter = 1;
                        $newFileName = $uploadFileName;

                        while (true) {
                            $pathInfo = pathinfo($uploadFileName);
                            $newFileName = $pathInfo['filename'] . " ($counter)." . $pathInfo['extension'];
                            $extension = $pathInfo['extension'];

                            $existingFile = File::where('name', $newFileName)
                                ->where('user_id', $user->id)
                                ->first();

                            if (!$existingFile) {
                                break;
                            }

                            $counter++;
                        }
                    } else {
                        $newFileName = $uploadFileName;
                    }

                    $fileId = Str::random(10);
                    $pathInfo = pathinfo($uploadFileName);
                    $extension = $pathInfo['extension'];
                    $uploadFile->storeAs('uploads', "$fileId.$extension");

                    $file = new File([
                        'user_id' => $user->id,
                        'file_id' => $fileId,
                        'name' => $newFileName,
                    ]);

                    $fileAccess = new FileAccess([
                        'user_id' => $user->id,
                        'file_id' => $fileId,
                        'type' => 'author'
                    ]);

                    $file->save();

                    $fileAccess->save();

                    $response = [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Success',
                        'name' => $newFileName,
                        'url' => url('api/files/' . $fileId),
                        'file_id' => $fileId,
                    ];
                }

                $responses[] = $response;
            }
        } else {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => [
                    'files' => ['field files cannot be blank'],
                ],
            ], 422);
        }

        return response()->json($responses);
    }


    public function deleteFile($fileId): JsonResponse
    {
        $file = File::where('file_id', $fileId)->first();

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

        $pathinfo = pathinfo($file->name);
        $extension = $pathinfo['extension'];
        $fullname = $file->file_id . '.' . $extension;

        Storage::delete("uploads/$fullname");

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

        $file = File::where('file_id', $file_id)->first();

        $pathInfo = pathinfo($file->name);
        $newFileName = $request->input('name') . '.' . $pathInfo['extension'];

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
            ->first();

        if ($existingFile && $existingFile->id !== $file->id) {
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
                'message' => $errors,
            ], 422);
        }

        $file->name = $newFileName;
        $file->save();

        return response()->json([
            "success" => true,
            "code" => 200,
            "message" => "Renamed"
        ]);
    }

    public function downloadFile($file_id): BinaryFileResponse|JsonResponse
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

        $access = FileAccess::where('file_id', $file_id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('type', 'co-author');
            })
            ->first();

        if (!$access) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Forbidden for you',
            ], 403);
        }

        $pathinfo = pathinfo($file->name);
        $extension = $pathinfo['extension'];
        $fullname = $file->file_id . '.' . $extension;

        $filePath = storage_path("app/uploads/$fullname");

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'File not found on server',
            ], 404);
        }

        return response()->download($filePath);
    }

    public function getUserFiles(): JsonResponse
    {
        $user = auth()->user();

        $files = File::where('user_id', $user->id)->with(['accesses.user'])->get();

        $response = [];

        foreach ($files as $file) {
            $accesses = $file->accesses->map(function ($access) {
                return [
                    'fullname' => $access->user->first_name . ' ' . $access->user->last_name,
                    'email' => $access->user->email,
                    'type' => $access->type,
                ];
            });

            $fileData = [
                'file_id' => $file->file_id,
                'name' => $file->name,
                'code' => 200,
                'url' => url("api/files/$file->file_id"),
                'accesses' => $accesses,
            ];

            $response[] = $fileData;
        }

        return response()->json($response);
    }

    public function getAccessedFiles(): JsonResponse
    {
        $user = auth()->user();

        $files = File::whereHas('accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('type', 'co-author');
        })->get();


        $response = [];

        foreach ($files as $file) {
            $fileData = [
                'file_id' => $file->file_id,
                'name' => $file->name,
                'code' => 200,
                'url' => url("api/files/$file->file_id")
            ];

            $response[] = $fileData;
        }

        return response()->json($response);
    }

}
