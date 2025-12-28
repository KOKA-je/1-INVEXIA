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
        Schema::create('historique_equipement', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipement_id')->constrained('equipements')->onDelete('cascade');
            $table->string('action'); // e.g., 'Mise en service', 'Attribution', 'Retour', 'Panne signalée', 'Réparation effectuée', 'Réforme'
            $table->text('details')->nullable(); // Additional details about the action
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('old_state')->nullable();
            $table->string('new_state')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who performed the action or to whom it was assigned
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_equipement');
    }
};