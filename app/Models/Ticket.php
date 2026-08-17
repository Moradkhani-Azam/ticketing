<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Policies\TicketPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;


#[UsePolicy(TicketPolicy::class)]
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'attachment_path',
        'attachment_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class);
    }

    public function externalApiAttempts(): HasMany
    {
        return $this->hasMany(ExternalApiAttempt::class);
    }
    
}