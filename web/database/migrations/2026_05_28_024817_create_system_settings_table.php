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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key, e.g. identity.university_name');
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'file'])->default('string');
            $table->string('group', 50)->default('general')->comment('identity|theme|attendance|notification');
            $table->string('label', 100)->nullable()->comment('Label yang ditampilkan di form admin');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
