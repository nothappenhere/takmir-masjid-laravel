<?php

use App\Helpers\ResponseHelper;

function apiResponse($request, $status, $success, $message, $data = null, $errors = null)
{
    return ResponseHelper::sendResponse($request, $status, $success, $message, $data, $errors);
}
