<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {

        $counts = Ticket::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'data' => [
                'total' => $counts->sum(),

                'pending_review' => $counts
                    ->get(TicketStatus::PendingReview->value, 0),
                'pending_level_two' => $counts
                    ->get(TicketStatus::PendingLevelTwo->value, 0),

                'sending' => $counts
                    ->get(TicketStatus::Sending->value, 0),

                'sent' => $counts
                    ->get(TicketStatus::Sent->value, 0),

                'send_failed' => $counts
                    ->get(TicketStatus::SendFailed->value, 0),

                'rejected' => $counts
                    ->get(TicketStatus::Rejected->value, 0),
            ],
        ]);
    }
}
