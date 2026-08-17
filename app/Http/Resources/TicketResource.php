<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'status' => $this->status->value,

            'attachment' => [
                'type' => $this->attachment_type,
                'url' => $this->attachment_path
                    ? asset('storage/' . $this->attachment_path)
                    : null,
            ],

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'can_reject' => $request->user()?->can('reject', $this->resource) ?? false,
            
            'can_approve' => $request->user()?->can('approve', $this->resource) ?? false,
            
            'history' => TicketStatusHistoryResource::collection(
                $this->whenLoaded('statusHistories')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
