<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estudiantes. No inician sesión, por eso no están en users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('institutional_id', 20)->unique();
            $table->string('first_names', 100);
            $table->string('last_names', 100);
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Orden habitual de listados y reportes.
            $table->index(['last_names', 'first_names']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
