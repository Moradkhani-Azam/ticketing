<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExternalWebServiceController extends Controller
{
    public function send(): JsonResponse
    {
        if (random_int(0, 1) === 1) {
            return response()->json([
                'message' => 'Send successful.',
            ], 200);
        }

        return response()->json([
            'message' => 'Internal server error.',
        ], 500);
    }
}
