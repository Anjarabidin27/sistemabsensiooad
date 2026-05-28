<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Default system settings untuk SIHADIR UDINUS.
     * Semua dapat diubah melalui Admin Panel → Pengaturan Sistem.
     */
    public function run(): void
    {
        $settings = [
            // ── Identitas Universitas ──────────────────────
            [
                'key'   => 'identity.university_name',
                'value' => 'Universitas Dian Nuswantoro',
                'type'  => 'string',
                'group' => 'identity',
                'label' => 'Nama Universitas',
            ],
            [
                'key'   => 'identity.university_short',
                'value' => 'UDINUS',
                'type'  => 'string',
                'group' => 'identity',
                'label' => 'Singkatan',
            ],
            [
                'key'   => 'identity.system_name',
                'value' => 'SIHADIR',
                'type'  => 'string',
                'group' => 'identity',
                'label' => 'Nama Sistem',
            ],
            [
                'key'   => 'identity.tagline',
                'value' => 'Sistem Informasi Kehadiran',
                'type'  => 'string',
                'group' => 'identity',
                'label' => 'Tagline',
            ],
            [
                'key'   => 'identity.website',
                'value' => 'https://dinus.ac.id',
                'type'  => 'string',
                'group' => 'identity',
                'label' => 'Website Universitas',
            ],
            [
                'key'   => 'identity.logo_path',
                'value' => 'images/logo_udinus.png',
                'type'  => 'file',
                'group' => 'identity',
                'label' => 'Logo Universitas',
            ],

            // ── Tampilan & Tema ────────────────────────────
            [
                'key'   => 'theme.primary_color',
                'value' => '#1B2A6B',
                'type'  => 'string',
                'group' => 'theme',
                'label' => 'Warna Utama',
            ],
            [
                'key'   => 'theme.accent_color',
                'value' => '#0EA5E9',
                'type'  => 'string',
                'group' => 'theme',
                'label' => 'Warna Aksen',
            ],
            [
                'key'   => 'theme.dark_mode',
                'value' => '0',
                'type'  => 'boolean',
                'group' => 'theme',
                'label' => 'Mode Gelap',
            ],
            [
                'key'   => 'theme.font_family',
                'value' => 'Inter',
                'type'  => 'string',
                'group' => 'theme',
                'label' => 'Font Utama',
            ],

            // ── Konfigurasi Presensi ───────────────────────
            [
                'key'   => 'attendance.confidence_threshold',
                'value' => '80',
                'type'  => 'integer',
                'group' => 'attendance',
                'label' => 'Threshold Confidence (%)',
            ],
            [
                'key'   => 'attendance.late_threshold_minutes',
                'value' => '15',
                'type'  => 'integer',
                'group' => 'attendance',
                'label' => 'Batas Terlambat (menit)',
            ],
            [
                'key'   => 'attendance.liveness_detection',
                'value' => '1',
                'type'  => 'boolean',
                'group' => 'attendance',
                'label' => 'Liveness Detection',
            ],
            [
                'key'   => 'attendance.allow_photo_upload',
                'value' => '1',
                'type'  => 'boolean',
                'group' => 'attendance',
                'label' => 'Izinkan Upload Foto',
            ],

            // ── Notifikasi ─────────────────────────────────
            [
                'key'   => 'notification.email_enabled',
                'value' => '1',
                'type'  => 'boolean',
                'group' => 'notification',
                'label' => 'Notifikasi Email',
            ],
            [
                'key'   => 'notification.timezone',
                'value' => 'Asia/Jakarta',
                'type'  => 'string',
                'group' => 'notification',
                'label' => 'Timezone',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('System settings seeded (' . count($settings) . ' settings).');
    }
}
