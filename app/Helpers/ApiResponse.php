<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data, $message = false)
    {
        if (is_string($data)) {
            $data['message'] = $data;
        }

        if (is_array($data) && $message) {
            $data['message'] = $message;
        }

        return response()->json(['data' => $data], 200);
    }

    public static function error($message)
    {
        return response()->json(['error' => $message], 422);
    }
}
