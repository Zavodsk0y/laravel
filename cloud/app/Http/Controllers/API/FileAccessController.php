<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileAccess;
use App\Models\User;
use Illuminate\Http\Request;

class FileAccessController extends Controller
{
    public function addAccessToFile($fileId, Request $request)
    {
        $email = $request->input('email');
        $user = auth()->user();

        $file = File::where('file_id', $fileId)
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

        $userAccess = User::where('email', $email)->first();

        if (!$userAccess) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'User with this email not found'
            ]);
        }

        $user = new FileAccess([
            'user_id' => $userAccess->id,
            'file_id' => $fileId,
            'type' => 'co-author'
        ]);

        $user->save();

        $fileAccesses = FileAccess::where('file_id', $fileId)->get();

        $usersWithAccess = [];
        foreach ($fileAccesses as $access) {
            $usersWithAccess[] = [
                'fullname' => $access->user->first_name . ' ' . $access->user->last_name,
                'email' => $access->user->email,
                'type' => $access->type,
                'code' => 200,
            ];
        }

        return response()->json($usersWithAccess);
    }
}
