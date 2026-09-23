<?php

use App\Enums\TicketStatus;
use App\Jobs\SendTicketToExternalApi;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

describe('ticket approval workflow', function () {

    it('moves a ticket through the complete approval workflow', function () {
        $levelOneAdmin = User::factory()
            ->adminLevelOne()
            ->create();

        $levelTwoAdmin = User::factory()
            ->adminLevelTwo()
            ->create();

        $ticket = Ticket::factory()
            ->pendingReview()
            ->create();

        /*
         * Level 1: pending_review -> pending_level_two
         */
        Queue::fake();

        Sanctum::actingAs($levelOneAdmin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve")
            ->assertSuccessful()
            ->assertJsonPath(
                'data.status',
                TicketStatus::PendingLevelTwo->value
            );

        expect($ticket->fresh()->status)
            ->toBe(TicketStatus::PendingLevelTwo);

        expect($ticket->statusHistories()->count())
            ->toBe(1);

        /*
         * Level 2: pending_level_two -> sending
         */
        Sanctum::actingAs($levelTwoAdmin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve")
            ->assertSuccessful()
            ->assertJsonPath(
                'data.status',
                TicketStatus::Sending->value
            );

        expect($ticket->fresh()->status)
            ->toBe(TicketStatus::Sending);

        expect($ticket->statusHistories()->count())
            ->toBe(2);

        /*
         * TicketStatusChanged should have caused
         * the external API job to be dispatched.
         */
        Queue::assertPushed(
            SendTicketToExternalApi::class,
            fn (SendTicketToExternalApi $job) =>
                $job->ticketId === $ticket->id
        );

        /*
         * External API: sending -> sent
         */
        Queue::fake();

        Http::fake([
            config('services.external_api.url') => Http::response([
                'success' => true,
            ], 200),
        ]);

        $job = new SendTicketToExternalApi($ticket->id);

        $job->handle();

        expect($ticket->fresh()->status)
            ->toBe(TicketStatus::Sent);
    });
});