<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        //
    }

    /**
     * Tangani semua error agar responsnya konsisten (pakai ResponseHelper)
     */
    public function render($request, Throwable $exception)
    {
        // Jika bukan request ke API, pakai tampilan default Laravel
        if (! $request->is('api/*')) {
            return parent::render($request, $exception);
        }

        // 🧩 1. Validasi Gagal
        if ($exception instanceof ValidationException) {
            return ResponseHelper::sendResponse(
                $request,
                422,
                false,
                'Validasi gagal',
                null,
                $exception->errors()
            );
        }

        // 🧩 2. Data Tidak Ditemukan
        if ($exception instanceof ModelNotFoundException) {
            return ResponseHelper::sendResponse(
                $request,
                404,
                false,
                'Data tidak ditemukan',
                null,
                ['detail' => $exception->getMessage()]
            );
        }

        // 🧩 3. Route Tidak Ditemukan
        if ($exception instanceof NotFoundHttpException) {
            return ResponseHelper::sendResponse(
                $request,
                404,
                false,
                'Endpoint tidak ditemukan',
                null,
                ['detail' => 'URL atau resource tidak tersedia.']
            );
        }

        // 🧩 4. Error Lain (default)
        return ResponseHelper::sendResponse(
            $request,
            500,
            false,
            'Terjadi kesalahan server',
            null,
            ['detail' => $exception->getMessage()]
        );
    }
}
