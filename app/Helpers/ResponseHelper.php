<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ResponseHelper
{
    /**
     * Send standardized JSON response
     */
    public static function sendResponse(
        Request $request,
        int $statusCode,
        bool $success,
        string $message,
        $data = null,
        $errors = null,
        array $meta = null
    ): JsonResponse {
        $response = [
            'success' => $success,
            'status' => $statusCode,
            'message' => $message,
            'path' => $request->path(),
            'method' => $request->method(),
            'timestamp' => Carbon::now()->toIso8601String(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        // Log error responses
        if (!$success && $statusCode >= 400) {
            Log::error('API Error Response', [
                'path' => $request->path(),
                'method' => $request->method(),
                'status' => $statusCode,
                'errors' => $errors,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Success response helper
     */
    public static function success(
        Request $request,
        $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = null
    ): JsonResponse {
        return self::sendResponse($request, $statusCode, true, $message, $data, null, $meta);
    }

    /**
     * Error response helper
     */
    public static function error(
        Request $request,
        string $message = 'Error',
        $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return self::sendResponse($request, $statusCode, false, $message, null, $errors);
    }

    /**
     * Validation error response
     */
    public static function validationError(
        Request $request,
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($request, $message, $errors, 422);
    }

    /**
     * Not found response
     */
    public static function notFound(
        Request $request,
        string $message = 'Resource not found'
    ): JsonResponse {
        return self::error($request, $message, null, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(
        Request $request,
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::error($request, $message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(
        Request $request,
        string $message = 'Access forbidden'
    ): JsonResponse {
        return self::error($request, $message, null, 403);
    }

    /**
     * Paginated response
     */
    public static function paginated(
        Request $request,
        $data,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $meta = [
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'last_page' => $data->lastPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'has_more_pages' => $data->hasMorePages(),
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
        ];

        return self::success($request, $data->items(), $message, 200, $meta);
    }
}
