<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'attachment_path' => 'tickets/test.pdf',
            'attachment_type' => 'application/pdf',
            'status' => TicketStatus::PendingReview,
        ];
    }

    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::PendingReview,
        ]);
    }

    public function pendingLevelTwo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::PendingLevelTwo,
        ]);
    }
}
