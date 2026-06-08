<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'code', 'name', 'credits', 'lecturer_name',
        'schedule_day', 'schedule_start', 'schedule_end', 'room', 'semester', 'is_active',
        'latitude', 'longitude', 'location_radius', 'location_required',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'location_required' => 'boolean',
        'latitude'          => 'float',
        'longitude'         => 'float',
        'location_radius'   => 'integer',
    ];

    /** Apakah mata kuliah ini sudah punya koordinat GPS. */
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /** Day name in Bahasa. */
    public function getDayNameAttribute(): string
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return $days[$this->schedule_day] ?? '-';
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
