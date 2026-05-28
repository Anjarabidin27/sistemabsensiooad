<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    /**
     * Get a setting value by key (with optional default).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        if (is_bool($value)) $value = $value ? '1' : '0';
        if (is_array($value)) $value = json_encode($value);

        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    /**
     * Get all settings in a group as key-value array.
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}
