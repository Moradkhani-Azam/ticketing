<?php

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Jobs\SendTicketToExternalApi;
use App\Listeners\SendTicketToExternalApiListener;
use App\Models\Ticket;
use Illuminate\Support\Facades\Queue;

describe('SendTicketToExternalApiListener', function () {

    it('dispatches the job when ticket status is sending', function () {
        Queue::fake();

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Sending,
        ]);

        $event = new TicketStatusChanged(
            ticket: $ticket,
            comment: 'Looks good.',
        );

        app(SendTicketToExternalApiListener::class)->handle($event);

        Queue::assertPushed(
            SendTicketToExternalApi::class,
            fn (SendTicketToExternalApi $job) =>
                $job->ticketId === $ticket->id
        );
    });

    it('does not dispatch the job when ticket status is not sending', function () {
        Queue::fake();

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::PendingReview,
        ]);

        $event = new TicketStatusChanged(
            ticket: $ticket,
            comment: 'Looks good.',
        );

        app(SendTicketToExternalApiListener::class)->handle($event);

        Queue::assertNothingPushed();
    });
});