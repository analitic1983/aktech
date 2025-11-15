<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\BusinessExceptionInterface;
use Illuminate\Contracts\Cache\LockTimeoutException as CacheLockTimeoutException;
use Illuminate\Database\LockTimeoutException as DatabaseLockTimeoutException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Формирует ответ в зависимости от типа запроса.
     */
    private function buildResponse(Request $request, string $message, int $status): HttpResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status, [], JSON_UNESCAPED_UNICODE);
        }

        return response($message, $status);
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof CacheLockTimeoutException ||
            $e instanceof DatabaseLockTimeoutException) {
            return $this->buildResponse(
                $request,
                'Please try again after 5 seconds.',
                HttpResponse::HTTP_REQUEST_TIMEOUT
            );
        }

        if ($e instanceof BusinessExceptionInterface) {
            return $this->buildResponse(
                $request,
                $e->getMessage(),
                HttpResponse::HTTP_CONFLICT
            );
        }

        return parent::render($request, $e);
    }
}
