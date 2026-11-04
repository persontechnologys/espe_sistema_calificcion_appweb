<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tres parciales por matrícula. NULL = pendiente; 0.00 = nota registrada.
 * Total, promedio y estado NO se almacenan: se derivan de los parciales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            // UNIQUE: como máximo un registro de notas por matrícula.
            $table->foreignId('enrollment_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('partial_1', 5, 2)->nullable();
            $table->decimal('partial_2', 5, 2)->nullable();
            $table->decimal('partial_3', 5, 2)->nullable();
            // Bloqueo optimista: se incrementa en cada guardado.
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        foreach ([1, 2, 3] as $n) {
            DB::statement("ALTER TABLE grades ADD CONSTRAINT grades_partial_{$n}_range_check CHECK (partial_{$n} IS NULL OR (partial_{$n} >= 0 AND partial_{$n} <= 20))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
