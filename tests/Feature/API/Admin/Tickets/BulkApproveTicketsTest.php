
<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use Illuminate\Support\Facades\Event;

describe('bulk approving tickets', function () {

    it('returns 401 for guests', function () {
        $ticket = Ticket::factory()->pendingReview()->create();

        $this->postJson('/api/admin/tickets/bulk-approve', [
            'ticket_ids' => [$ticket->id],
        ])->assertUnauthorized();
    });

    it('returns 403 when the user cannot bulk approve', function () {
        $ticket = Ticket::factory()->pendingReview()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/admin/tickets/bulk-approve', [
            'ticket_ids' => [$ticket->id],
        ])->assertForbidden();
    });

    it('returns 422 when ticket ids are missing', function () {
        Sanctum::actingAs(User::factory()->adminLevelOne()->create());

        $this->postJson('/api/admin/tickets/bulk-approve', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ticket_ids']);
    });

    it('returns 422 when a ticket id does not exist', function () {
        Sanctum::actingAs(User::factory()->adminLevelOne()->create());

        $this->postJson('/api/admin/tickets/bulk-approve', [
            'ticket_ids' => [999],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ticket_ids.0']);
    });

    it('returns 403 when any ticket is outside the admin level', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $allowed = Ticket::factory()->pendingReview()->create();
        $disallowed = Ticket::factory()->pendingLevelTwo()->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/tickets/bulk-approve', [
            'ticket_ids' => [$allowed->id, $disallowed->id],
        ])->assertForbidden();

        expect($allowed->fresh()->status)->toBe(TicketStatus::PendingReview);
        expect($disallowed->fresh()->status)->toBe(TicketStatus::PendingLevelTwo);
    });

    it('approves each pending review ticket for a level one admin', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $tickets = Ticket::factory()->pendingReview()->count(2)->create();

        Event::fake([TicketStatusChanged::class]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/tickets/bulk-approve', [
            'ticket_ids' => $tickets->pluck('id')->all(),
        ])
            ->assertSuccessful()
            ->assertJsonPath('message', 'Bulk approval completed.')
            ->assertJsonPath('data.approved', $tickets->pluck('id')->all())
            ->assertJsonPath('data.failed', []);

        $tickets->each(function (Ticket $ticket) {
            expect($ticket->fresh()->status)->toBe(TicketStatus::PendingLevelTwo);
        });

        Event::assertDispatchedTimes(TicketStatusChanged::class, 2);
    });
});
