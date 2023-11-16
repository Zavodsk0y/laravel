<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;

class ApiException extends HttpResponseException
{
    public function __construct($code = 422, $message = 'Нарушение правил валидации', $errors = [])
    {
        $data = [
            'success' => false,
            'code' => $code,
            'message' => $message,
        ];
        if (count($errors) > 0) {
            $data['error']['errors'] = $errors;
        }

        parent::__construct(response()->json($data)->setStatusCode($code));
    }
}
