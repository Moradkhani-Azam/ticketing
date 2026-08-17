<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = $request->user()
            ->tickets()
            ->latest()
            ->paginate($request->per_page);


        return TicketResource::collection(
            $tickets
        );
    }

    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $file = $request->file('attachment');

        $path = $file->store('tickets', 'public');

        $ticket = $request->user()->tickets()->create([
            'title' => $request->string('title'),
            'description' => $request->string('description'),
            'attachment_path' => $path,
            'attachment_type' => $file->getClientMimeType(),
            'status' => 'pending_review',
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => $ticket,
        ], 201);
    }
}
