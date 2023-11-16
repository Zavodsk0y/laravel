<?php

namespace App\Http\Validators;

use Illuminate\Support\Facades\Validator;

class FileValidator
{
    public static function validateFile($uploadFile): ?string
    {
        $rules = ['file' => 'required|file|mimes:doc,pdf,docx,zip,jpeg,jpg,png|max:2048'];
        $validator = Validator::make(['file' => $uploadFile], $rules);

        return $validator->fails()
            ? $validator->errors()->first()
            : null;
    }
}
