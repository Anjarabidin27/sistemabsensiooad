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
        Schema::create('recognition_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable()->comment('NULL jika tidak dikenali');
            $table->string('image_hash', 64)->nullable()->comment('SHA-256 dari gambar');
            $table->enum('result', ['recognized', 'unknown', 'error', 'spoofing', 'no_face']);
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_id', 'created_at']);
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recognition_logs');
    }
};
