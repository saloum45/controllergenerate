<?php

namespace App\Traits;

trait GenerateApiResponse
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected function successResponse($data = null, $message = 'Succès', $code = 200)
    {
        return response()->json([
            'status_code' => $code,
            'status_message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Erreur', $code = 500, $error = null)
    {
        return response()->json([
            'status_code' => $code,
            'status_message' => $message,
            'error' => $error
        ], $code);
    }
}
