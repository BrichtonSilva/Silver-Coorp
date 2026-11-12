<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_provider_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', ['a_contacter', 'contacte', 'confirme', 'annule'])->default('a_contacter');
            $table->timestamps();

            $table->unique(['event_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_provider_assignments');
    }
};
