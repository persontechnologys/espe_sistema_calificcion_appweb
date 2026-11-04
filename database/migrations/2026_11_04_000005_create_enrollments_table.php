<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            // Impide matrícula duplicada incluso con solicitudes simultáneas.
            $table->unique(['student_id', 'course_offering_id']);
            // Listado de matriculados de una oferta.
            $table->index('course_offering_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
