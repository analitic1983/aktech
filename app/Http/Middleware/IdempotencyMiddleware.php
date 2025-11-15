<?php

namespace App\Http\Middleware;

use App\Modules\Slots\Checkers\IdempotencyChecker;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function __construct(
        private readonly IdempotencyChecker $idempotencyChecker,
    ) {
    }

    /**
     * @throws \Throwable
     * @throws LockTimeoutException
     */
    public function handle(Request $request, Closure $next, string $operation): Response
    {
        $idempotencyKey = (string) $request->header('Idempotency-Key', '');
        if (!Str::isUuid($idempotencyKey)) {
            return $this->jsonFail([
                'message' => 'Idempotency-Key header must be a valid UUID.',
            ], 422);
        }

        $payloadHash = hash('sha256', $request->url() . json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $extendedKey = $idempotencyKey . ':' . $payloadHash;
        [$locked, $cached] = $this->idempotencyChecker->lockAndGetPrevious($operation, $extendedKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            /** @var Response $response */
            $response = $next($request);
        } finally {
            $locked->release();
        }

        if ($this->shouldStoreResponse($response)) {
            /** @var JsonResponse $response */
            $this->idempotencyChecker->store($operation, $extendedKey, $response);
        }

        return $response;
    }

    private function shouldStoreResponse(Response $response): bool
    {
        if (!$response instanceof JsonResponse) {
            return false;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return false;
        }

        $payload = $response->getData(true);

        return is_array($payload) && ($payload['success'] ?? false) === true;
    }

    private function jsonFail(array $dataArray, int $code)
    {
        return response()->json([
            'success' => false,
            ...$dataArray,
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }
}
