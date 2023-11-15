<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $e)
    {
        if ($e instanceof AuthorizationException) {
            throw new ApiException(403, "Запрет доступа");
        }
        if ($e instanceof ModelNotFoundException) {
            throw new ApiException(404, 'Не найдено');
        }
        if ($e instanceof AuthenticationException) {
            throw new ApiException(403, 'Ошибка авторизации');
        }
        return parent::render($request, $e);
    }

    public function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if($e instanceof ValidationException && $request->expectsJson()) {
            return response()->json([
                "success" => false,
                "code" => 422,
                "message" => $e->errors(),
            ], 422);
        } else {
            parent::convertValidationExceptionToResponse($e, $request);
        }
    }
}
