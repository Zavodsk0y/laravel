<?php

namespace App\Http\Services;

use App\Exceptions\ApiException;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\FileAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class FileService
{
    public function processFile($uploadFile)
    {
        $validationError = $this->validateFile($uploadFile);

        if ($validationError) {
            return [
                'success' => false,
                'message' => $validationError,
                'name' => $uploadFile->getClientOriginalName(),
            ];
        }

        $uploadFileName = $uploadFile->getClientOriginalName();

        $existingFile = File::where('name', $uploadFileName)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingFile) {
            $uploadFileName = $this->makeUniqueFileName($uploadFileName, Auth::id());
        }

        $fileId = Str::random(10);
        $pathInfo = pathinfo($uploadFileName);
        $extension = $pathInfo['extension'];

        $uploadFile->storeAs('uploads', "$fileId.$extension");

        $file = new File([
            'user_id' => Auth::id(),
            'file_id' => $fileId,
            'name' => $uploadFileName,
        ]);

        $fileAccess = new FileAccess([
            'user_id' => Auth::id(),
            'file_id' => $fileId,
            'type' => 'author',
        ]);

        $data[] = $file;

        $file->save();
        $fileAccess->save();

        return FileResource::collection($data);
    }

    public function deleteFile($fileId)
    {
        $file = File::where('file_id', $fileId)->first();

        if (!$file) {
            throw new ApiException(404, 'File not found');
        }

        if ($file->user_id !== Auth::id()) {
            throw new ApiException(403, 'Forbidden for you');
        }

        $pathinfo = pathinfo($file->name);
        $extension = $pathinfo['extension'];
        $fullname = $file->file_id . '.' . $extension;

        Storage::delete("uploads/$fullname");

        $file->delete();

        return [
            'success' => true,
            'code' => 200,
            'message' => 'File deleted',
        ];
    }

    public function updateFileName($newFileName, $fileId): array
    {
        $file = File::where('file_id', $fileId)->first();

        if (!$file) {
            throw new ApiException(404, 'File not found');
        }

        if ($file->user_id !== Auth::id()) {
            throw new ApiException(403, 'Forbidden for you');
        }

        $existingFile = File::where('name', $newFileName)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingFile && $existingFile->id !== $file->id) {
            return [
                'success' => false,
                'code' => 422,
                'message' => 'Name already exists',
            ];
        }

        $ext = pathinfo($file->name, PATHINFO_EXTENSION);
        $file->name = "$newFileName.$ext";
        $file->save();

        return [
            "success" => true,
            "code" => 200,
            "message" => "Renamed",
        ];
    }

    public function downloadFile($fileId, $file): array|string
    {
        $user = auth()->user();

        if (!$file) {
            throw new ApiException(404, 'File not found');
        }

        $access = FileAccess::where('file_id', $fileId)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('type', 'co-author');
            })
            ->first();

        if (!$access) {
            throw new ApiException(403, 'Forbidden for you');
        }

        $pathinfo = pathinfo($file->name);
        $extension = $pathinfo['extension'];
        $fullname = $file->file_id . '.' . $extension;

        $filePath = storage_path("app/uploads/$fullname");

        if (!file_exists($filePath)) {
            throw new ApiException(404, 'File not found on server');
        }

        return $filePath;
    }

    public function getUserFiles($files)
    {
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

        return $response;
    }

    public function getAccessedFiles()
    {
        $user = Auth::user();

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

        return $response;
    }

    protected function makeUniqueFileName($fileName, $userId): string
    {
        $counter = 1;

        do {
            $pathInfo = pathinfo($fileName);
            $newFileName = $pathInfo['filename'] . " ($counter)." . $pathInfo['extension'];

            $existingFile = File::where('name', $newFileName)
                ->where('user_id', $userId)
                ->first();

            $counter++;
        } while ($existingFile);

        return $newFileName;
    }

    public function validateFile($uploadFile): string|null
    {
        $rules = ['file' => 'required|file|mimes:doc,pdf,docx,zip,jpeg,jpg,png|max:2048'];
        $validator = Validator::make(['file' => $uploadFile], $rules);

        return $validator->fails()
            ? $validator->errors()->first()
            : null;
    }
}
