<?php

namespace App\Listeners;

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Jobs\SendTicketToExternalApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketToExternalApiListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TicketStatusChanged $event): void
    {
        if ($event->ticket->status !== TicketStatus::Sending) {
            return;
        }

        SendTicketToExternalApi::dispatch($event->ticket->id);
    }
}
