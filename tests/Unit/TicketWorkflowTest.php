<?php

namespace Tests\Unit;

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Http\Controllers\Api\Admin\TicketController;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_approved(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $admin = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => TicketStatus::PendingReview,
        ]);

        $service = app(TicketController::class);

        $updatedTicket = $service->changeStatus(
            ticket: $ticket,
            status: TicketStatus::PendingLevelTwo,
            admin: $admin,
            comment: 'Ticket approved.',
        );

        $this->assertEquals(
            TicketStatus::PendingLevelTwo,
            $updatedTicket->status
        );

        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'from_status' => TicketStatus::PendingReview->value,
            'to_status' => TicketStatus::PendingLevelTwo->value,
            'comment' => 'Ticket approved.',
        ]);

        Event::assertDispatched(
            TicketStatusChanged::class,
            function ($event) use ($ticket) {
                return $event->ticket->id === $ticket->id
                    && $event->comment === 'Ticket approved.';
            }
        );
    }
}
