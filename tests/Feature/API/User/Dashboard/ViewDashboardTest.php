<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('viewing user dashboard', function () {
    it('returns 401 when no token is provided', function () {
        $this->getJson('/api/dashboard')
            ->assertUnauthorized();
    });

    it('returns ticket counts for the authenticated user', function () {
        $user = User::factory()->create();

        Ticket::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::PendingReview,
        ]);

        Ticket::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::PendingLevelTwo,
        ]);

        Ticket::factory()->count(4)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::Sending,
        ]);

        Ticket::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::Sent,
        ]);

        Ticket::factory()->count(1)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::SendFailed,
        ]);

        Ticket::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::Rejected,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
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

    it('does not include tickets belonging to other users', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ticket::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::PendingReview,
        ]);

        Ticket::factory()->count(10)->create([
            'user_id' => $otherUser->id,
            'status' => TicketStatus::PendingReview,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
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

    it('returns zero for statuses with no tickets', function () {
        $user = User::factory()->create();

        Ticket::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => TicketStatus::PendingReview,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
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