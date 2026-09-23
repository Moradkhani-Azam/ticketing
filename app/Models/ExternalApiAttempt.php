<?php

namespace App\Models;

use App\Enums\ExternalApiAttemptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalApiAttempt extends Model
{
    protected $fillable = [
        'ticket_id',
        'attempt_number',
        'status',
        'http_status',
        'response_body',
        'error_message',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExternalApiAttemptStatus::class,
            'attempted_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
