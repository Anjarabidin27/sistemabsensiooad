<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'code', 'name', 'credits', 'lecturer_name',
        'schedule_day', 'schedule_start', 'schedule_end', 'room', 'semester', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
