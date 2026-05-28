<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecognitionLog extends Model
{
    // Table name is explicitly defined
    protected $table = 'recognition_logs';

    // Disable standard timestamps (only created_at is managed via database default)
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'image_hash',
        'result',
        'confidence_score',
        'processing_time_ms',
        'error_message',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
