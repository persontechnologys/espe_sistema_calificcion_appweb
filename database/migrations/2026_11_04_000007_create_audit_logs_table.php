<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de calificaciones. Solo inserciones (sin updated_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->string('operation', 20);           // registro | correccion
            $table->json('old_values')->nullable();     // NULL si la nota se crea por primera vez
            $table->json('new_values');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('enrollment_id');
            $table->index('created_at');
        });

        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_operation_check CHECK (operation IN ('registro', 'correccion'))");
        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_reason_check CHECK (operation <> 'correccion' OR reason IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
