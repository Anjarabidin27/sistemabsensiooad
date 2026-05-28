@extends('layouts.admin')

@section('title', 'Tambah Mahasiswa')
@section('header-title', 'Tambah Mahasiswa Baru')
@section('header-subtitle', 'Daftarkan profil mahasiswa baru ke database akademik')

@section('content')

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <span class="card-title">Form Input Mahasiswa</span>
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

            <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="student_number" class="form-label">NIM Mahasiswa <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="student_number" name="student_number" class="form-control" placeholder="A11.2023.15023" value="{{ old('student_number') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Nugroho Anjar Abidin" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Kampus / Pribadi</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="nugroho@mhs.dinus.ac.id" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password Login Portal <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="program_study" class="form-label">Program Studi</label>
                        <input type="text" id="program_study" name="program_study" class="form-control" placeholder="Teknik Informatika" value="{{ old('program_study', 'Teknik Informatika') }}">
                    </div>

                    <div class="form-group">
                        <label for="faculty" class="form-label">Fakultas</label>
                        <input type="text" id="faculty" name="faculty" class="form-control" placeholder="Ilmu Komputer" value="{{ old('faculty', 'Ilmu Komputer') }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="enrollment_year" class="form-label">Tahun Angkatan</label>
                        <input type="number" id="enrollment_year" name="enrollment_year" class="form-control" placeholder="2023" value="{{ old('enrollment_year', date('Y')) }}">
                    </div>

                    <div class="form-group">
                        <label for="photo" class="form-label">Foto Profil (Avatar)</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" checked>
                        Akun Mahasiswa Aktif
                    </label>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary">Simpan Profil Mahasiswa</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batalkan</a>
                </div>

            </form>
        </div>
    </div>

@endsection
