<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('showing a ticket', function () {
    it('returns 401 for guests', function () {
        $ticket = Ticket::factory()->create();

        $this->getJson("/api/admin/tickets/{$ticket->id}")
            ->assertUnauthorized();
    });

    it('returns 403 when the user cannot view tickets', function () {
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/admin/tickets/{$ticket->id}")
            ->assertForbidden();
    });

    it('returns the ticket and submitting user', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs($admin);

        $this->getJson("/api/admin/tickets/{$ticket->id}")
            ->assertSuccessful()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', $ticket->title)
            ->assertJsonPath('data.user.id', $ticket->user_id);
    });
});
