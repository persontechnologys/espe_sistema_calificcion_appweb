<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Usuarios que inician sesión: administradores y docentes.
 * Un docente es un usuario con role = 'teacher' (no existe tabla teachers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email')->unique();
            $table->string('password');
            // string + CHECK + enum de PHP (no $table->enum(), que difiere entre motores).
            $table->string('role', 20);
            $table->boolean('is_active')->default(true);
            // ID institucional (texto). NULL permitido para administradores;
            // UNIQUE admite varios NULL en PostgreSQL y MySQL.
            $table->string('institutional_id', 20)->nullable()->unique();
            $table->rememberToken();
            $table->timestamps();
        });

        // CHECK en SQL estándar: idéntico en PostgreSQL y MySQL 8.0.16+.
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'teacher'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_teacher_institutional_id_check CHECK (role <> 'teacher' OR institutional_id IS NOT NULL)");

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
        Schema::dropIfExists('users');
    }
};
