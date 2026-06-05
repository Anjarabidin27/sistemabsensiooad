<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'student_number', 'name', 'email', 'photo_path',
        'program_study', 'faculty', 'enrollment_year', 'password', 'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
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

    /** Foto URL (public/storage) dengan fallback ke foto biometrik atau avatar default. */
    public function getPhotoUrlAttribute(): string
    {
        // 1. Cek jika mahasiswa mengunggah foto profil sendiri (berada di folder profiles/)
        if ($this->photo_path && str_starts_with($this->photo_path, 'profiles/')) {
            return asset('storage/' . $this->photo_path);
        }
        
        // 2. Fallback ke foto registrasi biometrik dari AI Engine jika ada
        $embedding = $this->faceEmbeddings()->first();
        if ($embedding && $embedding->photo_path) {
            return asset('storage/' . $embedding->photo_path);
        }

        // 3. Fallback ke general photo_path (misalnya jika diisi manual di DB)
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }

        return asset('images/default-avatar.png');
    }
}
