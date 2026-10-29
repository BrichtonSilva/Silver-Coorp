<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Module M1 (Accès et utilisateurs) : table des utilisateurs et table des sessions.
 * Remplace la table « users » fournie par Laravel : seuls les champs du cahier des charges (§6.7) existent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            // Unique et insensible à la casse : normalisé en minuscules à l'enregistrement (R02) + index unique en dernier rempart.
            $table->string('identifiant', 100)->unique();
            $table->string('mot_de_passe');
            $table->string('role', 30);
            $table->boolean('actif')->default(true);
            $table->timestamp('derniere_connexion')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('utilisateurs');
    }
};
