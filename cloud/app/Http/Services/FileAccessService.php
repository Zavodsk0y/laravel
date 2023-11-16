<?php

namespace App\Http\Services;

use App\Exceptions\ApiException;
use App\Models\File;
use App\Models\FileAccess;
use App\Models\User;


class FileAccessService
{
    public function addAccessToFile(File $file, string $email): array
    {
        $userAccess = User::where('email', $email)->first();

        if (!$userAccess) {
            throw new ApiException(404, 'User with this email not found');
        }

        $existingAccess = FileAccess::where('file_id', $file->file_id)
            ->where('user_id', $userAccess->id)
            ->first();

        if ($existingAccess) {
            throw new ApiException(422, 'User already has access to this file');
        }

        FileAccess::create([
            'user_id' => $userAccess->id,
            'file_id' => $file->file_id,
            'type' => 'co-author'
        ]);

        $fileAccesses = FileAccess::where('file_id', $file->file_id)->get();

        $usersWithAccess = [];
        foreach ($fileAccesses as $access) {
            $usersWithAccess[] = [
                'fullname' => "{$access->user->first_name} {$access->user->last_name}",
                'email' => $access->user->email,
                'type' => $access->type,
                'code' => 200,
            ];
        }

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Access granted',
            'data' => $usersWithAccess,
        ];
    }

    public function deleteAccessToFile(File $file, string $email)
    {
        if (!$file) {
            throw new ApiException(404, 'File not found');
        }

        if (auth()->user()->email === $email) {
            throw new ApiException(403, 'Forbidden to remove yourself');
        }

        $userAccess = User::where('email', $email)->first();

        if (!$userAccess) {
            throw new ApiException(404, 'User with this email not found');
        }

        $fileAccess = FileAccess::where('user_id', $userAccess->id)
            ->where('file_id', $file->file_id)
            ->where('type', 'co-author')
            ->first();

        if (!$fileAccess) {
            throw new ApiException(404, 'User is not a co-author of file');
        }

        $fileAccess->delete();

        $allAccesses = FileAccess::where('file_id', $file->file_id)->get();
        $accesses = [];

        foreach ($allAccesses as $access) {
            $user = $access->user;

            if ($user->email !== $email) {
                $accesses[] = [
                    'fullname' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'code' => 200,
                    'type' => $access->type,
                ];
            }
        }

        return ['accesses' => $accesses, 'code' => 200];
    }
}
