<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Enums\TicketStatus;


describe('creating a ticket', function () {

    it('returns 401 for guests', function () {
        $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'description' => 'Test description',
        ])->assertUnauthorized();
    });

    it('rejects an unsupported attachment type', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create(
            'attachment.txt',
            100,
            'text/plain'
        );

        $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'description' => 'Test description',
            'attachment' => $file,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attachment']);
    });

    it('creates a ticket', function () {
        Storage::fake('public');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create(
            'attachment.pdf',
            100,
            'application/pdf'
        );

        $response = $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'description' => 'Test description',
            'attachment' => $file,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Ticket created successfully.',
            ]);

        $ticket = Ticket::query()->first();

        expect($ticket)
            ->not->toBeNull()
            ->and($ticket->user_id)->toBe($user->id)
            ->and($ticket->title)->toBe('Test ticket')
            ->and($ticket->description)->toBe('Test description')
            ->and($ticket->status)->toBe(TicketStatus::PendingReview);

        Storage::disk('public')->assertExists(
            $ticket->attachment_path
        );
    });
});
