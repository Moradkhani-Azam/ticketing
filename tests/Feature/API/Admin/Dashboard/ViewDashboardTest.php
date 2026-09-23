<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('viewing dashboard', function () {

    it('returns 401 when no token is provided', function () {
        $this->getJson('/api/admin/dashboard')
            ->assertUnauthorized();
    });

    it('returns 403 when the user cannot view the dashboard', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/admin/dashboard')
            ->assertForbidden();
    });

    it('returns ticket counts grouped by status', function () {
        $admin = User::factory()->create();

        $admin->givePermissionTo('ticket.view-all');

        Ticket::factory()
            ->count(3)
            ->create([
                'status' => TicketStatus::PendingReview,
            ]);

        Ticket::factory()
            ->count(2)
            ->create([
                'status' => TicketStatus::PendingLevelTwo,
            ]);

        Ticket::factory()
            ->count(4)
            ->create([
                'status' => TicketStatus::Sending,
            ]);

        Ticket::factory()
            ->count(2)
            ->create([
                'status' => TicketStatus::Sent,
            ]);

        Ticket::factory()
            ->count(1)
            ->create([
                'status' => TicketStatus::SendFailed,
            ]);

        Ticket::factory()
            ->count(2)
            ->create([
                'status' => TicketStatus::Rejected,
            ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'total' => 14,
                    'pending_review' => 3,
                    'pending_level_two' => 2,
                    'sending' => 4,
                    'sent' => 2,
                    'send_failed' => 1,
                    'rejected' => 2,
                ],
            ]);
    });

    it('returns zero for statuses with no tickets', function () {
        $admin = User::factory()->create();

        $admin->givePermissionTo('ticket.view-all');

        Ticket::factory()
            ->count(3)
            ->create([
                'status' => TicketStatus::PendingReview,
            ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'total' => 3,
                    'pending_review' => 3,
                    'pending_level_two' => 0,
                    'sending' => 0,
                    'sent' => 0,
                    'send_failed' => 0,
                    'rejected' => 0,
                ],
            ]);
    });

});