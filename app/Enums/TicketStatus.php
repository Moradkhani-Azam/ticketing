<?php

namespace App\Enums;


enum TicketStatus: string
{
    case PendingReview = 'pending_review';
    case PendingLevelTwo = 'pending_level_two';
    case Rejected = 'rejected';
    case Sending = 'sending';
    case SendFailed = 'send_failed';
    case Sent = 'sent';
}
