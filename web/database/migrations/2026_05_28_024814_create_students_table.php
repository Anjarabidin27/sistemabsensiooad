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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number', 20)->unique()->comment('NIM Mahasiswa');
            $table->string('name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('photo_path', 255)->nullable()->comment('Foto profil mahasiswa');
            $table->string('program_study', 100)->nullable()->comment('Program Studi');
            $table->string('faculty', 100)->nullable()->comment('Fakultas');
            $table->year('enrollment_year')->nullable()->comment('Tahun masuk');
            $table->string('password')->nullable()->comment('Password untuk login student');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
