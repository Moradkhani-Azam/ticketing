<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Enums\ExternalApiAttemptStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Throwable;

class SendTicketToExternalApi implements ShouldQueue
{
    use Queueable;

    public int $tries = 0;

    public Ticket $ticket;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $ticketId)
    {
        $this->ticket = Ticket::findOrFail($this->ticketId);
    }

    public function backoff(): int
    {
        return (int) config('services.external_api.retry_delay', 3600);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $attemptNumber = $this->ticket
            ->externalApiAttempts()
            ->max('attempt_number') + 1;

        $attempt = $this->ticket->externalApiAttempts()->create([
            'attempt_number' => $attemptNumber,
            'status' => ExternalApiAttemptStatus::Started,
            'attempted_at' => now(),
        ]);

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post(config('services.external_api.url'), [
                    'ticket_id' => $this->ticket->id,
                    'title' => $this->ticket->title,
                    'description' => $this->ticket->description,
                ]);

            if ($response->successful()) {
                $attempt->update([
                    'status' => ExternalApiAttemptStatus::Successful,
                    'http_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                $this->ticket->update([
                    'status' => TicketStatus::Sent,
                ]);

                return;
            }

            $response->throw();
        } catch (Throwable $exception) {

            $attempt->update([
                'status' => ExternalApiAttemptStatus::Failed,
                'http_status' => isset($response) ? $response->status() : null,
                'response_body' => isset($response) ? $response->body() : null,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->ticket->update([
            'status' => TicketStatus::SendFailed,
        ]);
    }
}
