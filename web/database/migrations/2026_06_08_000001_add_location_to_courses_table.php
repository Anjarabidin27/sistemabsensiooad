<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom koordinat GPS dan konfigurasi lokasi ke tabel courses.
     * Digunakan untuk verifikasi Haversine saat mahasiswa melakukan presensi.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('room')
                ->comment('Koordinat lintang ruang kelas (GPS)');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')
                ->comment('Koordinat bujur ruang kelas (GPS)');
            $table->smallInteger('location_radius')->default(100)->after('longitude')
                ->comment('Radius toleransi presensi dalam meter');
            $table->boolean('location_required')->default(false)->after('location_radius')
                ->comment('Wajibkan verifikasi lokasi GPS saat presensi?');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'location_radius', 'location_required']);
        });
    }
};
