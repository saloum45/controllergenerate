<?php

namespace App\Traits;

trait GenerateApiResponse
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected function successResponse($data = null, $message = 'Succès', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null
        ], $code);
    }

    protected function errorResponse($message = 'Erreur', $code = 500, $error = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $error
        ], $code);
    }
}
