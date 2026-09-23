<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


describe('listing tickets', function () {
    it('returns 401 when no token is provided', function () {
        $this->getJson('/api/admin/tickets')
            ->assertUnauthorized();
    });

    it('returns 403 when the user cannot view all tickets', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/tickets')
            ->assertForbidden();
    });

    it('returns the latest tickets with the submitting user', function () {
        $admin = User::factory()->adminLevelOne()->create();
        $older = Ticket::factory()->create(['created_at' => now()->subDay()]);
        $newer = Ticket::factory()->create(['created_at' => now()]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/tickets')
            ->assertSuccessful()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.0.user.id', $newer->user_id)
            ->assertJsonPath('data.0.can_approve', true);
    });
});
