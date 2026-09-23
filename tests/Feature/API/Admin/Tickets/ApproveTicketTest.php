
<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use Illuminate\Support\Facades\Event;


describe('approving a ticket', function () {

    it('returns 401 for guests', function () {
        $ticket = Ticket::factory()->pendingReview()->create();

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve")
            ->assertUnauthorized();
    });

    it('returns 403 when the admin cannot act on the ticket status', function (string $factoryState, TicketStatus $status) {
        $admin = User::factory()->{$factoryState}()->create();
        $ticket = Ticket::factory()->create(['status' => $status]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve")
            ->assertForbidden();

        expect($ticket->fresh()->status)->toBe($status);
    })->with([
        'level one on pending level two' => ['adminLevelOne', TicketStatus::PendingLevelTwo],
        'level two on pending review' => ['adminLevelTwo', TicketStatus::PendingReview],
    ]);

    it('returns 422 when the comment is longer than 2000 characters', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $ticket = Ticket::factory()->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve", [
            'comment' => str_repeat('a', 2001),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['comment']);

        expect($ticket->fresh()->status)->toBe(TicketStatus::PendingReview);
    });

    it('moves a pending review ticket to pending level two for a level one admin', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $ticket = Ticket::factory()->pendingReview()->create();

        Event::fake([TicketStatusChanged::class]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve")
            ->assertSuccessful()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.status', TicketStatus::PendingLevelTwo->value);

        expect($ticket->fresh()->status)->toBe(TicketStatus::PendingLevelTwo);

        $history = $ticket->statusHistories()->first();

        expect($history)
            ->user_id->toBe($admin->id)
            ->from_status->toBe(TicketStatus::PendingReview->value)
            ->to_status->toBe(TicketStatus::PendingLevelTwo->value)
            ->comment->toBe('Ticket approved by level one.');

        Event::assertDispatched(
            TicketStatusChanged::class,
            fn(TicketStatusChanged $event) => $event->ticket->is($ticket)
                && $event->comment === 'Ticket approved by level one.'
        );
    });

    it('moves a pending level two ticket to sending for a level two admin', function () {
        $admin = User::factory()->adminLevelTwo()->create();
        $ticket = Ticket::factory()->pendingLevelTwo()->create();

        Event::fake([TicketStatusChanged::class]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/approve", [
            'comment' => 'Looks good.',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.status', TicketStatus::Sending->value)
            ->assertJsonPath('data.history.0.comment', 'Looks good.');

        expect($ticket->fresh()->status)->toBe(TicketStatus::Sending);

        Event::assertDispatched(
            TicketStatusChanged::class,
            fn(TicketStatusChanged $event) => $event->ticket->is($ticket)
                && $event->comment === 'Looks good.'
        );
    });
});
