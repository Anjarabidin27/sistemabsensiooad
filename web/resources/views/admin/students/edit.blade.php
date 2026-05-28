@extends('layouts.admin')

@section('title', 'Edit Mahasiswa')
@section('header-title', 'Edit Mahasiswa')
@section('header-subtitle', 'Perbarui detail profil akademik mahasiswa')

@section('content')

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <span class="card-title">Edit Profil: {{ $student->name }}</span>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
        
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <ul style="padding-left: 20px; font-size: 0.85rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="student_number" class="form-label">NIM Mahasiswa <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="student_number" name="student_number" class="form-control" value="{{ old('student_number', $student->student_number) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Kampus / Pribadi</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password Login Portal</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="program_study" class="form-label">Program Studi</label>
                        <input type="text" id="program_study" name="program_study" class="form-control" value="{{ old('program_study', $student->program_study) }}">
                    </div>

                    <div class="form-group">
                        <label for="faculty" class="form-label">Fakultas</label>
                        <input type="text" id="faculty" name="faculty" class="form-control" value="{{ old('faculty', $student->faculty) }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="enrollment_year" class="form-label">Tahun Angkatan</label>
                        <input type="number" id="enrollment_year" name="enrollment_year" class="form-control" value="{{ old('enrollment_year', $student->enrollment_year) }}">
                    </div>

                    <div class="form-group">
                        <label for="photo" class="form-label">Perbarui Foto Profil</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div style="display: flex; gap: 16px; align-items: center; margin-top: 10px;">
                    <img src="{{ $student->photo_url }}" alt="Current Photo" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-color);">
                    <span style="font-size: 0.775rem; color: var(--text-muted);">Foto saat ini</span>
                </div>

                <div class="form-group" style="margin-top: 18px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" {{ $student->is_active ? 'checked' : '' }}>
                        Akun Mahasiswa Aktif
                    </label>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batalkan</a>
                </div>

            </form>
        </div>
    </div>

@endsection
