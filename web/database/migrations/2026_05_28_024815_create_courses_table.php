<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Kode mata kuliah');
            $table->string('name', 100)->comment('Nama mata kuliah');
            $table->integer('credits')->default(3)->comment('Jumlah SKS');
            $table->string('lecturer_name', 100)->nullable()->comment('Nama dosen');
            $table->tinyInteger('schedule_day')->nullable()->comment('0=Senin, 6=Minggu');
            $table->time('schedule_start')->nullable();
            $table->time('schedule_end')->nullable();
            $table->string('room', 50)->nullable()->comment('Ruangan');
            $table->string('semester', 20)->nullable()->comment('e.g. 2024/2025-Genap');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
