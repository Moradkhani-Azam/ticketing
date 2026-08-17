<?php

use App\Http\Controllers\Api\Admin\TicketController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->group(function () {
        
        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->can('viewAny', Ticket::class);

        Route::post('/tickets/bulk-approve', [
            TicketController::class,
            'bulkApprove',
        ])->can('bulkApprove', Ticket::class);

        Route::get('/tickets', [TicketController::class, 'index'])
            ->can('viewAny', Ticket::class);

        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
            ->can('view', 'ticket');

        Route::post('/tickets/{ticket}/approve', [TicketController::class, 'approve'])
            ->can('approve', 'ticket');

        Route::post('/tickets/{ticket}/reject', [TicketController::class, 'reject'])
            ->can('reject', 'ticket');
    });
