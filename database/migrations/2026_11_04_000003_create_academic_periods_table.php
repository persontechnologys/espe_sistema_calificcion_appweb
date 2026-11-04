<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();        // ej. 202451
            $table->string('description', 150);          // ej. PREGRADO S-II OCT 24 - MAR 25
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('planned');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE academic_periods ADD CONSTRAINT academic_periods_status_check CHECK (status IN ('planned', 'open', 'closed'))");
        DB::statement('ALTER TABLE academic_periods ADD CONSTRAINT academic_periods_dates_check CHECK (end_date >= start_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
