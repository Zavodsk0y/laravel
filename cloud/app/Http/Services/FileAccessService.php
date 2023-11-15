<?php

namespace App\Http\Services;

use App\Models\File;
use App\Models\FileAccess;
use App\Models\User;


class FileAccessService
{
    public function addAccessToFile(File $file, User $userAccess): array
    {
        $user = auth()->user();

        if ($file->user_id !== $user->id) {
            return [
                'success' => false,
                'code' => 403,
                'message' => 'Forbidden for you',
            ];
        }

        $existingAccess = FileAccess::where('file_id', $file->file_id)
            ->where('user_id', $userAccess->id)
            ->first();

        if ($existingAccess) {
            return [
                'success' => false,
                'code' => 422,
                'message' => 'User already has access to this file',
            ];
        }

        $newAccess = new FileAccess([
            'user_id' => $userAccess->id,
            'file_id' => $file->file_id,
            'type' => 'co-author'
        ]);

        $newAccess->save();

        $fileAccesses = FileAccess::where('file_id', $file->file_id)->get();

        $usersWithAccess = [];
        foreach ($fileAccesses as $access) {
            $usersWithAccess[] = [
                'fullname' => $access->user->first_name . ' ' . $access->user->last_name,
                'email' => $access->user->email,
                'type' => $access->type,
                'code' => 200,
            ];
        }

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Access granted',
            'data' => $fileAccesses, // На доработке
        ];
    }

    public function deleteAccessToFile(File $file, string $email)
    {
        $user = auth()->user();

        if (!$file) {
            return [
                'success' => false,
                'code' => 404,
                'message' => 'File not found',
            ];
        }

        if ($user->email === $email) {
            return [
                'success' => false,
                'code' => 403,
                'message' => 'Forbidden to remove yourself',
            ];
        }

        $userAccess = User::where('email', $email)->first();

        if (!$userAccess) {
            return [
                'success' => false,
                'code' => 404,
                'message' => 'User with this email not found',
            ];
        }

        $fileAccess = FileAccess::where('user_id', $userAccess->id)
            ->where('file_id', $file->file_id)
            ->where('type', 'co-author')
            ->first();

        if (!$fileAccess) {
            return [
                'success' => false,
                'code' => 404,
                'message' => 'User is not a co-author of this file',
            ];
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
