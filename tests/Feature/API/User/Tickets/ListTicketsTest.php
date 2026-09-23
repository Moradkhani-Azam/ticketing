<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('listing tickets', function () {

    it('returns 401 for guests', function () {
        $this->getJson('/api/tickets')
            ->assertUnauthorized();
    });

    it('returns only the authenticated user tickets', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ticket::factory()
            ->count(3)
            ->for($user)
            ->create();

        Ticket::factory()
            ->count(2)
            ->for($otherUser)
            ->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('returns tickets ordered by latest', function () {
        $user = User::factory()->create();

        $oldTicket = Ticket::factory()
            ->for($user)
            ->create([
                'created_at' => now()->subDay(),
            ]);

        $newTicket = Ticket::factory()
            ->for($user)
            ->create([
                'created_at' => now(),
            ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonPath('data.0.id', $newTicket->id)
            ->assertJsonPath('data.1.id', $oldTicket->id);
    });

    it('paginates tickets', function () {
        $user = User::factory()->create();

        Ticket::factory()
            ->count(15)
            ->for($user)
            ->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/tickets?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10);
    });
});