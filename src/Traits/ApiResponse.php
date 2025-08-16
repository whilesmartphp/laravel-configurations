<?php

namespace Whilesmart\ModelConfiguration\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    use Loggable;

    /**
     * Return a success response.
     */
    protected function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a failure response.
     */
    protected function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        $this->error($message, $errors);

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
