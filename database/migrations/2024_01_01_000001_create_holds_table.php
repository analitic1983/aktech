<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('slot_uuid');
            $table->foreign('slot_uuid')
                ->references('uuid')
                ->on('slots')
                ->cascadeOnDelete();
            $table->enum('status', ['held', 'confirmed', 'cancelled']);
            $table->string('idempotency_key')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('created_at');
            $table->index(['slot_uuid', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
