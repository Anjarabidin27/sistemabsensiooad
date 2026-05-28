<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'student_id', 'course_id', 'check_in_at',
        'confidence_score', 'status', 'image_path', 'ip_address', 'notes',
    ];

    protected $casts = [
        'check_in_at'      => 'datetime',
        'confidence_score' => 'decimal:4',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'present'  => '<span class="badge-present">Hadir</span>',
            'late'     => '<span class="badge-late">Terlambat</span>',
            'rejected' => '<span class="badge-rejected">Ditolak</span>',
            default    => '<span class="badge-unknown">Unknown</span>',
        };
    }

    public function getConfidencePercentAttribute(): string
    {
        return number_format((float) $this->confidence_score * 100, 1) . '%';
    }
}
