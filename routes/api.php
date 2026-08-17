<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExternalWebServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/external-service/send', [
    ExternalWebServiceController::class,
    'send',
]);

Route::middleware('auth:sanctum')
    ->get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin
                ],
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    });


Route::middleware('auth:sanctum')
    ->group(function () {
        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ]);

        Route::get('/tickets', [TicketController::class, 'index']);
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    });
