@extends('layouts.student')

@section('title', 'Profil Mahasiswa')

@section('content')
    <!-- Top Header -->
    <header class="student-header" style="padding-bottom: 30px;">
        <div class="student-header-top" style="margin-bottom: 0;">
            <a href="{{ route('student.home') }}" style="color: white; font-size: 1.1rem;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 1.05rem;">Profil Saya</span>
            <div style="width: 20px;"></div> <!-- Spacer -->
        </div>
    </header>

    <div class="student-body">
        
        <!-- Status & Flash Message Alert -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
        
        <!-- Profile Card -->
        <div class="card" style="text-align: center; padding: 24px 0;">
            <div class="card-body" style="display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 0 20px;">
                <!-- Profile Image with Camera Upload Overlay -->
                <div style="position: relative; width: 96px; height: 96px; margin: 0 auto;">
                    <img src="{{ $student->photo_url }}" alt="Profile Photo" style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-light); box-shadow: var(--shadow-md);">
                    <label for="profile_photo_input" style="position: absolute; bottom: 0; right: 0; background-color: var(--accent); color: var(--primary-dark); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; cursor: pointer; box-shadow: var(--shadow-sm); transition: var(--transition);">
                        <i class="fa-solid fa-camera" style="font-size: 0.85rem;"></i>
                    </label>
                </div>
                
                <form id="profile-photo-form" action="{{ route('student.profile.upload') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    <input type="file" id="profile_photo_input" name="profile_photo" accept="image/*" onchange="document.getElementById('profile-photo-form').submit();">
                </form>
                
                <div>
                    <h3 style="font-weight: 800; font-size: 1.15rem; color: var(--primary-dark);">{{ $student->name }}</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">NIM: {{ $student->student_number }}</p>
                </div>

                @if($student->faceEmbeddings()->exists())
                    <span class="badge badge-present" style="font-size: 0.7rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-check"></i>
                        Wajah Terdaftar
                    </span>
                @else
                    <span class="badge badge-rejected" style="font-size: 0.7rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        Wajah Belum Terdaftar
                    </span>
                @endif
            </div>
        </div>

        <!-- Academic Info -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Informasi Akademik</span>
            </div>
            <div class="card-body" style="padding: 12px 20px;">
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">Program Studi</span>
                    <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-main);">{{ $student->program_study ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">Fakultas</span>
                    <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-main);">{{ $student->faculty ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">Tahun Angkatan</span>
                    <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-main);">{{ $student->enrollment_year ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0;">
                    <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">Email Kampus</span>
                    <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-main);">{{ $student->email ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div style="margin-top: 10px;">
            <form action="{{ route('student.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-full">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i>
                    Keluar Akun
                </button>
            </form>
        </div>

    </div>
@endsection
