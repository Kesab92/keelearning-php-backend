<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

/**
 * Formats the error message in a way that the frontend app can understand it.
 */
class APIError extends JsonResponse
{
    public function __construct($message, $status = 500, $headers = [], $options = 0)
    {
        $data = [
            'message' => $message,
        ];
        parent::__construct($data, $status, $headers, $options);
    }
}
