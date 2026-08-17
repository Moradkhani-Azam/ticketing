<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketStatusNotification
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
        $event->ticket->user->notify(
            new TicketStatusChangedNotification(
                ticket: $event->ticket,
                comment: $event->comment,
            )
        );
    }
}
