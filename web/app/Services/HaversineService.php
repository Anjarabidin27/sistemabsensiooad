<?php

namespace App\Services;

/**
 * SIHADIR — Haversine Service
 *
 * Menghitung jarak antara dua titik koordinat GPS di permukaan bumi
 * menggunakan rumus Haversine.
 *
 * Digunakan untuk memverifikasi bahwa mahasiswa berada dalam radius
 * yang diizinkan dari ruang kelas saat melakukan presensi wajah.
 *
 * Rumus:
 *   a = sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlon/2)
 *   c = 2·atan2(√a, √(1−a))
 *   d = R · c   (R = 6.371.000 meter)
 */
class HaversineService
{
    /** Radius bumi dalam meter. */
    private const EARTH_RADIUS_METERS = 6_371_000;

    /**
     * Hitung jarak antara dua koordinat GPS menggunakan rumus Haversine.
     *
     * @param  float  $lat1  Lintang titik pertama (mahasiswa)
     * @param  float  $lon1  Bujur titik pertama (mahasiswa)
     * @param  float  $lat2  Lintang titik kedua (ruang kelas)
     * @param  float  $lon2  Bujur titik kedua (ruang kelas)
     * @return float  Jarak dalam meter (dibulatkan 2 desimal)
     */
    public static function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Konversi derajat ke radian
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $lon1Rad = deg2rad($lon1);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Komponen rumus Haversine
        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Periksa apakah posisi mahasiswa berada dalam radius yang diizinkan.
     *
     * @param  float  $studentLat    Lintang posisi mahasiswa
     * @param  float  $studentLon    Bujur posisi mahasiswa
     * @param  float  $courseLat     Lintang ruang kelas
     * @param  float  $courseLon     Bujur ruang kelas
     * @param  int    $radiusMeters  Radius toleransi dalam meter
     * @return array{is_within: bool, distance: float, radius: int}
     */
    public static function isWithinRadius(
        float $studentLat,
        float $studentLon,
        float $courseLat,
        float $courseLon,
        int $radiusMeters
    ): array {
        $distance = self::distance($studentLat, $studentLon, $courseLat, $courseLon);

        return [
            'is_within' => $distance <= $radiusMeters,
            'distance'  => $distance,
            'radius'    => $radiusMeters,
        ];
    }
}
