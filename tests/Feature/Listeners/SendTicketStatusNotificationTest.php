<?php

use App\Events\TicketStatusChanged;
use App\Listeners\SendTicketStatusNotification;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

describe('SendTicketStatusNotification', function () {

    it('sends a notification to the ticket owner', function () {
        Notification::fake();

        $user = User::factory()->create();

        $ticket = Ticket::factory()
            ->pendingReview()
            ->create([
                'user_id' => $user->id,
            ]);

        $event = new TicketStatusChanged(
            ticket: $ticket,
            comment: 'Please review this ticket.',
        );

        (new SendTicketStatusNotification())->handle($event);

        Notification::assertSentTo(
            $user,
            TicketStatusChangedNotification::class,
            fn (TicketStatusChangedNotification $notification) =>
                $notification->ticket->is($ticket)
                && $notification->comment === 'Please review this ticket.'
        );
    });
});