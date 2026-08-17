<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Collection;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return  $user->can('ticket.view-all');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return  $user->can('ticket.view-all');
    }

    public function approve(User $user, Ticket $ticket): bool
    {
        return $user->can('ticket.approve')
            && $this->canActOnTicket($user, $ticket);
    }

    public function reject(User $user, Ticket $ticket): bool
    {
        return $user->can('ticket.reject')
            && $this->canActOnTicket($user, $ticket);
    }

    public function bulkApprove(User $user): bool
    {
        return $user->can('ticket.bulk-approve');
    }

    public function canBulkApproveTickets(User $user, Collection $tickets): bool
    {
        return $tickets->every(
            fn(Ticket $ticket) => $this->canActOnTicket($user, $ticket)
        );
    }

    private function canActOnTicket(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('admin_level_1')) {
            return $ticket->status === TicketStatus::PendingReview;
        }

        if ($user->hasRole('admin_level_2')) {
            return $ticket->status === TicketStatus::PendingLevelTwo;
        }

        return false;
    }
}
