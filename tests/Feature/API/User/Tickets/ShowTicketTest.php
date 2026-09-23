<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('showing a ticket', function () {

    it('returns 401 for guests', function () {
        $ticket = Ticket::factory()->create();

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertUnauthorized();
    });

    it('returns the ticket', function () {
        $user = User::factory()->create();

        $ticket = Ticket::factory()
            ->for($user)
            ->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    });

    it('returns the ticket resource fields', function () {
        $user = User::factory()->create();

        $ticket = Ticket::factory()
            ->for($user)
            ->create([
                'title' => 'My ticket',
                'description' => 'Ticket description',
            ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', 'My ticket')
            ->assertJsonPath('data.description', 'Ticket description')
            ->assertJsonPath('data.status', $ticket->status->value)
            ->assertJsonPath('data.attachment.type', $ticket->attachment_type)
            ->assertJsonPath('data.can_approve', false)
            ->assertJsonPath('data.can_reject', false)
            ->assertJsonPath('data.created_at', $ticket->created_at->toISOString())
            ->assertJsonPath('data.updated_at', $ticket->updated_at->toISOString());
    });

    it('does not allow the user to view another users ticket', function () {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $ticket = Ticket::factory()
            ->for($otherUser)
            ->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertNotFound();
    });

    it('returns 404 when the ticket does not exist', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/tickets/999999')
            ->assertNotFound();
    });
});
