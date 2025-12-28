<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pannes', function (Blueprint $table) {
            $table->id();
            $table->string('lib_pan');
            $table->string('lib_cat')->nullable();
            $table->string('sta_pan');
            $table->string('diag_pan')->nullable();
            $table->string('action_pan')->nullable();
            $table->date('date_signa');
            $table->date('date_dt')->nullable();
            $table->date('date_rsl')->nullable();
            $table->date('date_an')->nullable();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('equipement_id')->nullable()
                ->constrained('equipements')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pannes');
    }
};