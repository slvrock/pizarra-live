
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_strokes', function (Blueprint $table) {

            // Identificador unico de cada dibujo
            $table->uuid('stroke_uuid')->unique();

            // Herramienta utilizada
            $table->string('tool', 20);

            // Color seleccionado
            $table->string('color', 7);

            // Grosor del trazo
            $table->unsignedTinyInteger('size');

            // Coordenadas del dibujo
            $table->json('points');

        });
    }

    public function down(): void
    {
        Schema::table('board_strokes', function (Blueprint $table) {

            $table->dropUnique(['stroke_uuid']);

            $table->dropColumn([
                'stroke_uuid',
                'tool',
                'color',
                'size',
                'points'
            ]);

        });
    }
};
