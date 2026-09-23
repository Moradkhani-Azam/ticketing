<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;

describe('TicketStatusChangedNotification', function () {

    it('builds an approval email with the comment', function () {
        $user = User::factory()->create();

        $ticket = Ticket::factory()
            ->pendingReview()
            ->create([
                'user_id' => $user->id,
                'status' => TicketStatus::PendingLevelTwo,
            ]);

        $notification = new TicketStatusChangedNotification(
            ticket: $ticket,
            comment: 'Looks good.',
        );

        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('Ticket status updated')
            ->and($mail->introLines)
            ->toContain('Your ticket has been approved.')
            ->toContain("Ticket: {$ticket->title}")
            ->toContain('Comment: Looks good.');
    });

    it('builds a rejection email without a comment', function () {
        $user = User::factory()->create();

        $ticket = Ticket::factory()
            ->pendingReview()
            ->create([
                'user_id' => $user->id,
                'status' => TicketStatus::Rejected,
            ]);

        $notification = new TicketStatusChangedNotification(
            ticket: $ticket,
        );

        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('Ticket status updated')
            ->and($mail->introLines)
            ->toContain('Your ticket has been rejected.')
            ->toContain("Ticket: {$ticket->title}");
    });
});