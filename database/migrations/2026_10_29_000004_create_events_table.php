<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->enum('type', ['mariage', 'séminaire', 'conférence', 'concert', 'autre']);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('venue_id')->constrained('venues');
            $table->integer('guest_count');
            $table->decimal('budget', 12, 2);
            $table->enum('status', ['brouillon', 'confirmé', 'terminé', 'annulé'])->default('brouillon');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('charged_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
