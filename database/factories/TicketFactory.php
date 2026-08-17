<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Enums\TicketStatus;

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
}
