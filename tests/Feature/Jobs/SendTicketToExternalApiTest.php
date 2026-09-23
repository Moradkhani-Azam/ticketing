<?php

use App\Enums\ExternalApiAttemptStatus;
use App\Enums\TicketStatus;
use App\Jobs\SendTicketToExternalApi;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;

describe('SendTicketToExternalApi job', function () {

    it('marks the ticket as sent when external api succeeds', function () {
        Http::fake([
            '*' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Sending,
        ]);

        $job = new SendTicketToExternalApi($ticket->id);

        $job->handle();

        $ticket->refresh();

        expect($ticket->status)
            ->toBe(TicketStatus::Sent);

        $attempt = $ticket
            ->externalApiAttempts()
            ->first();

        expect($attempt)
            ->not->toBeNull()
            ->and($attempt->attempt_number)->toBe(1)
            ->and($attempt->status)
            ->toBe(ExternalApiAttemptStatus::Successful)
            ->and($attempt->http_status)->toBe(200);
    });

    it('records a failed attempt when external api returns an error', function () {
        Http::fake([
            '*' => Http::response([
                'message' => 'Internal Server Error',
            ], 500),
        ]);

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Sending,
        ]);

        $job = new SendTicketToExternalApi($ticket->id);

        expect(fn() => $job->handle())
            ->toThrow(\Illuminate\Http\Client\RequestException::class);

        $attempt = $ticket
            ->externalApiAttempts()
            ->first();

        expect($attempt)
            ->not->toBeNull()
            ->and($attempt->attempt_number)->toBe(1)
            ->and($attempt->status)
            ->toBe(ExternalApiAttemptStatus::Failed)
            ->and($attempt->http_status)
            ->toBe(500);
    });


    it('increments the attempt number', function () {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Sending,
        ]);

        $ticket->externalApiAttempts()->create([
            'attempt_number' => 1,
            'status' => ExternalApiAttemptStatus::Failed,
            'attempted_at' => now(),
        ]);

        $ticket->externalApiAttempts()->create([
            'attempt_number' => 2,
            'status' => ExternalApiAttemptStatus::Failed,
            'attempted_at' => now(),
        ]);

        $job = new SendTicketToExternalApi($ticket->id);

        $job->handle();

        $attempt = $ticket
            ->externalApiAttempts()
            ->latest('attempt_number')
            ->first();

        expect($attempt->attempt_number)
            ->toBe(3);
    });

    it('marks the ticket as send failed when the job permanently fails', function () {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Sending,
        ]);

        $job = new SendTicketToExternalApi($ticket->id);

        $job->failed(
            new RuntimeException('External API failed.')
        );

        expect($ticket->fresh()->status)
            ->toBe(TicketStatus::SendFailed);
    });
});
