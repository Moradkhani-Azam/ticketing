<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_api_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained()
                ->cascadeOnDelete();
                
            $table->unsignedInteger('attempt_number');

            $table->string('status');

            $table->unsignedSmallInteger('http_status')->nullable();

            $table->text('response_body')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('attempted_at');

            $table->timestamps();

            $table->index([
                'ticket_id',
                'attempt_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_api_attempts');
    }
};
