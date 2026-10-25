<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_participants', function (Blueprint $table) {

            $table->id();

            // Identificador de la sesion del participante
            $table->uuid('participant_uuid')->unique();

            // Apodo elegido
            $table->string('name', 30);

            // Ultima actividad registrada
            $table->timestamp('last_seen')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_participants');
    }
};
