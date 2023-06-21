<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data, $message = false)
    {
        $responseData = [];

        if (is_string($data)) {
            $responseData['message'] = $data;
        }

        if (is_array($data)) {
            $responseData = $data;
            if ($message) {
                $responseData['message'] = $message;
            }
        }

        return response()->json(['data' => $responseData], 200, [], JSON_NUMERIC_CHECK);
    }

    public static function error($message)
    {
        return response()->json(['error' => $message], 422, [], JSON_NUMERIC_CHECK);
    }
}
