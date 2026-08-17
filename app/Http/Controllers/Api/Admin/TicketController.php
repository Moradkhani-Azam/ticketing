<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkApproveTicketsRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->with(['user'])
            ->latest()
            ->paginate($request->per_page);

        return TicketResource::collection($tickets);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $ticket->load(['user']);

        return new TicketResource($ticket);
    }

    public function approve(Request $request, Ticket $ticket): TicketResource
    {
        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->processApproval($ticket, $request->user(), $validated['comment'] ?? null);

        return new TicketResource($ticket->fresh(['user', 'statusHistories.user']));
    }

    public function reject(Request $request, Ticket $ticket): TicketResource
    {
        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->changeStatus(
            ticket: $ticket,
            status: TicketStatus::Rejected,
            user: $request->user(),
            comment: $validated['comment'] ?? null,
        );

        return new TicketResource($ticket->fresh(['user']));
    }

    public function bulkApprove(BulkApproveTicketsRequest $request): JsonResponse
    {
        $ticketIds = $request->validated('ticket_ids');
        $user = $request->user();
        $comment = $request->validated('comment');

        $tickets = Ticket::query()
            ->whereIn('id', $ticketIds)
            ->get();

        Gate::authorize('canBulkApproveTickets', [Ticket::class, $tickets]);

        $approved = [];
        $failed = [];

        foreach ($tickets as $ticket) {
            try {
                $this->processApproval($ticket, $user, $comment);
                $approved[] = $ticket->id;
            } catch (Throwable $exception) {
                $failed[] = [
                    'id' => $ticket->id,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk approval completed.',
            'data' => [
                'approved' => $approved,
                'failed' => $failed,
            ],
        ]);
    }

    private function processApproval(Ticket $ticket, User $user, ?string $customComment = null): void
    {
        if ($user->hasRole('admin_level_2')) {
            $status = TicketStatus::Sending;
            $defaultComment = 'Ticket approved by level two.';
        } else {
            $status = TicketStatus::PendingLevelTwo;
            $defaultComment = 'Ticket approved by level one.';
        }

        $this->changeStatus(
            ticket: $ticket,
            status: $status,
            user: $user,
            comment: $customComment ?? $defaultComment,
        );
    }

    public function changeStatus(
        Ticket $ticket,
        TicketStatus $status,
        ?User $user = null,
        ?string $comment = null
    ): Ticket {
        return DB::transaction(function () use ($ticket, $status, $user, $comment) {
            $oldStatus = $ticket->status;

            if ($oldStatus === $status) {
                return $ticket;
            }

            $ticket->update(['status' => $status]);

            $ticket->statusHistories()->create([
                'user_id' => $user?->id,
                'from_status' => $oldStatus->value,
                'to_status' => $status->value,
                'comment' => $comment,
            ]);

            TicketStatusChanged::dispatch(
                $ticket,
                $comment
            );

            return $ticket;
        });
    }
}
