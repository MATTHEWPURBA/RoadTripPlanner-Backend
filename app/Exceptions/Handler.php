<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Additional custom reporting logic here if needed
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // If the request expects JSON, format all exceptions as JSON responses
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return standardized JSON responses
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleApiException($request, Throwable $e): JsonResponse
    {
        // Log the exception with detailed information
        $this->logDetailedException($e);

        // Handle specific exception types
        if ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->jsonResponse([
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist'
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->jsonResponse([
                'message' => 'Endpoint not found',
                'error' => 'The requested endpoint does not exist'
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->jsonResponse([
                'message' => 'Method not allowed',
                'error' => 'The HTTP method used is not supported for this endpoint'
            ], 405);
        }

        if ($e instanceof AuthenticationException) {
            return $this->jsonResponse([
                'message' => 'Unauthenticated',
                'error' => 'You are not authenticated to access this resource'
            ], 401);
        }

    // Handle all other exceptions with a generic error response
    $statusCode = 500; // Default to 500
    
    // Only try to get status code from exceptions that have this method
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
        $statusCode = $e->getStatusCode();
    }

              // Log with appropriate level based on severity
        //   $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        // Apply the same change to any other place in the file where you call getStatusCode().



        $response = [
            'message' => 'An error occurred',
            'error' => $this->getErrorMessage($e, $statusCode)
        ];

        // Include exception details in non-production environments
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return [
                        'file' => $trace['file'] ?? null,
                        'line' => $trace['line'] ?? null,
                        'function' => $trace['function'] ?? null,
                        'class' => $trace['class'] ?? null,
                    ];
                })->take(10)->toArray()
            ];
        }

        return $this->jsonResponse($response, $statusCode);
    }

    /**
     * Create a standardized JSON response
     * 
     * @param  array  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function jsonResponse(array $data, int $statusCode): JsonResponse
    {
        return new JsonResponse($data, $statusCode, [
            'Content-Type' => 'application/json',
            'X-Error-Code' => $statusCode
        ]);
    }

    /**
     * Get appropriate error message based on status code and exception
     * 
     * @param  \Throwable  $e
     * @param  int  $statusCode
     * @return string
     */
    private function getErrorMessage(Throwable $e, int $statusCode): string
    {
        // Provide user-friendly messages based on status code
        $messages = [
            400 => 'Bad request',
            401 => 'Unauthorized access',
            403 => 'Forbidden access',
            404 => 'Resource not found',
            405 => 'Method not allowed',
            419 => 'CSRF token mismatch',
            422 => 'Validation failed',
            429 => 'Too many requests',
            500 => 'Server error',
            503 => 'Service unavailable'
        ];

        // Return appropriate message or fallback to exception message in debug mode
        return $messages[$statusCode] ?? (config('app.debug') ? $e->getMessage() : 'An unexpected error occurred');
    }

    /**
     * Log detailed exception information
     * 
     * @param  \Throwable  $e
     * @return void
     */
    private function logDetailedException(Throwable $e): void
    {
        // Create a structured log entry with detailed exception information
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'previous' => $e->getPrevious() ? [
                'exception' => get_class($e->getPrevious()),
                'message' => $e->getPrevious()->getMessage()
            ] : null
        ];

        // Include request information when available
        if (request()) {
            $context['request'] = [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => request()->except(['password', 'password_confirmation', 'current_password'])
            ];
        }

    // Handle all other exceptions with a generic error response
    $statusCode = 500; // Default to 500
    
    // Only try to get status code from exceptions that have this method
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
        $statusCode = $e->getStatusCode();
    }

          // Log with appropriate level based on severity
        //   $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        // Apply the same change to any other place in the file where you call getStatusCode().

        
        if ($statusCode >= 500) {
            Log::error('Server Error', $context);
        } elseif ($statusCode >= 400) {
            Log::warning('Client Error', $context);
        } else {
            Log::info('Exception', $context);
        }
    }
}



// This file is part of the Laravel framework.
// app/Exceptions/Handler.php