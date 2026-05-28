<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'student_number', 'name', 'email', 'photo_path',
        'program_study', 'faculty', 'enrollment_year', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Relasi ke kehadiran. */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Relasi ke embedding wajah. */
    public function faceEmbeddings(): HasMany
    {
        return $this->hasMany(FaceEmbedding::class);
    }

    /** Apakah mahasiswa sudah punya embedding wajah. */
    public function hasFaceRegistered(): bool
    {
        return $this->faceEmbeddings()->exists();
    }

    /** Foto URL (public/storage). */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && file_exists(public_path('storage/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }
        return asset('images/default-avatar.png');
    }
}
