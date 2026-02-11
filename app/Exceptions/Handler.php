<?php

namespace App\Exceptions;

use App\Exceptions\Custom\OrderException;
use App\Exceptions\Custom\PaymentException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle unauthenticated users for API
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    private function handleApiException($request, Throwable $exception)
    {
        // Handle Authentication Exception
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Handle Validation Exception
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        // Handle Order/Payment Exceptions
        if ($exception instanceof OrderException || $exception instanceof PaymentException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }

        // Handle Not Found
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        }

        // Handle all other exceptions
        $statusCode = method_exists($exception, 'getStatusCode') 
            ? $exception->getStatusCode() 
            : 500;

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage() ?: 'Internal server error',
        ], $statusCode);
    }
}