<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

/**
 * SIHADIR — Settings Service
 *
 * Cached access to system_settings table.
 * Provides typed helpers for all configurable values.
 */
class SettingsService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY = 'sihadir_settings_all';

    /** Get all settings as flat array (cached). */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SystemSetting::all()->pluck('value', 'key')->toArray();
        });
    }

    /** Get typed setting value. */
    public function get(string $key, mixed $default = null): mixed
    {
        return SystemSetting::get($key, $default);
    }

    /** Save and invalidate cache. */
    public function set(string $key, mixed $value): void
    {
        SystemSetting::set($key, $value);
        Cache::forget(self::CACHE_KEY);
    }

    /** Bulk save settings from form array. */
    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value);
        }
        Cache::forget(self::CACHE_KEY);
    }

    // ── Typed helpers ────────────────────────────────────────

    public function universityName(): string
    {
        return $this->get('identity.university_name', 'Universitas Dian Nuswantoro');
    }

    public function universityShort(): string
    {
        return $this->get('identity.university_short', 'UDINUS');
    }

    public function systemName(): string
    {
        return $this->get('identity.system_name', 'SIHADIR');
    }

    public function tagline(): string
    {
        return $this->get('identity.tagline', 'Sistem Informasi Kehadiran');
    }

    public function logoPath(): string
    {
        $path = $this->get('identity.logo_path', '');
        return $path ?: 'images/logo_udinus.png';
    }

    public function primaryColor(): string
    {
        return $this->get('theme.primary_color', '#1B2A6B');
    }

    public function accentColor(): string
    {
        return $this->get('theme.accent_color', '#0EA5E9');
    }

    public function confidenceThreshold(): float
    {
        return (float) $this->get('attendance.confidence_threshold', 80) / 100;
    }

    public function lateThresholdMinutes(): int
    {
        return (int) $this->get('attendance.late_threshold_minutes', 15);
    }

    public function livenessEnabled(): bool
    {
        return (bool) $this->get('attendance.liveness_detection', true);
    }

    public function allowPhotoUpload(): bool
    {
        return (bool) $this->get('attendance.allow_photo_upload', true);
    }
}
