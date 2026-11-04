<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oferta académica (paralelo) identificada por NRC dentro de un período.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            // Que el usuario tenga rol teacher se valida en el Form Request.
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('nrc', 20);
            $table->string('classroom', 50)->nullable();
            $table->string('campus', 100)->nullable();
            $table->timestamps();

            // Decisión 18.1: el NRC es único dentro de un período.
            $table->unique(['academic_period_id', 'nrc']);
            // "Mis ofertas" del docente (las FK ya crean índice en MySQL; en PostgreSQL no).
            $table->index('teacher_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};
