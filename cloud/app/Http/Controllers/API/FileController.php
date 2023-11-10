<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function uploadFiles(Request $request)
    {
        $files = $request->file('files'); // Получение массива файлов
        $user = auth()->user(); // Получаем текущего пользователя

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
                    $jsonResps[] = $response;
                } else {
                    $fileId = Str::random(10);
                    $uploadFile->store('uploads/');

                    $file = $uploadFile;
                    $file = new File([
                        'user_id' => $user->id,
                        'file_id' => $fileId,
                        'name' => $file->getClientOriginalName(), // Имя файла
                    ]);

                    $file->save();

                    $response = [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Success',
                        'name' => $uploadFile->getClientOriginalName(),
                        'url' => url('api/files/' . $fileId),
                        'file_id' => $fileId,
                    ];

                    $jsonResps[] = $response;


                }
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
}
