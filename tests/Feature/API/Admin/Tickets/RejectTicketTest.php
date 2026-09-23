
<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use Illuminate\Support\Facades\Event;


describe('rejecting a ticket', function () {
    it('returns 401 for guests', function () {
        $ticket = Ticket::factory()->pendingReview()->create();

        $this->postJson("/api/admin/tickets/{$ticket->id}/reject")
            ->assertUnauthorized();
    });

    it('returns 403 when the admin cannot reject the ticket', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $ticket = Ticket::factory()->pendingLevelTwo()->create();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/reject")
            ->assertForbidden();

        expect($ticket->fresh()->status)->toBe(TicketStatus::PendingLevelTwo);
    });

    it('marks a pending review ticket as rejected', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $ticket = Ticket::factory()->pendingReview()->create();

        Event::fake([TicketStatusChanged::class]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/tickets/{$ticket->id}/reject", [
            'comment' => 'Incomplete documents.',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.status', TicketStatus::Rejected->value);

        expect($ticket->fresh()->status)->toBe(TicketStatus::Rejected);

        $history = $ticket->statusHistories()->first();

        expect($history)
            ->user_id->toBe($admin->id)
            ->from_status->toBe(TicketStatus::PendingReview->value)
            ->to_status->toBe(TicketStatus::Rejected->value)
            ->comment->toBe('Incomplete documents.');

        Event::assertDispatched(
            TicketStatusChanged::class,
            fn(TicketStatusChanged $event) => $event->ticket->is($ticket)
                && $event->comment === 'Incomplete documents.'
        );
    });
});
