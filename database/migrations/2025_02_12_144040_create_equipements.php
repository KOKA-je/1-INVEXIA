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
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('num_serie_eq')->nullable();
            $table->string('num_inventaire_eq')->nullable();
            $table->string('nom_eq');
            $table->string('designation_eq')->nullable();
            $table->string('etat_eq')->nullable();
            $table->string('statut_eq')->nullable();
            $table->dateTime('date_acq');
            $table->foreignId('user_id')->nullable()
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('categorie_equipement_id')->nullable()
                ->constrained('categorie_equipements')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};