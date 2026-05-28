@extends('layouts.admin')

@section('title', 'Tambah Mata Kuliah')
@section('header-title', 'Tambah Mata Kuliah')
@section('header-subtitle', 'Buat jadwal mata kuliah baru beserta slot ruangan perkuliahan')

@section('content')

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <span class="card-title">Form Input Mata Kuliah</span>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-sm">
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

            <form action="{{ route('admin.courses.store') }}" method="POST">
                @csrf
                
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="code" class="form-label">Kode Mata Kuliah <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" placeholder="A11.3503" value="{{ old('code') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Mata Kuliah <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Object Oriented Analysis and Design" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="credits" class="form-label">Jumlah SKS <span style="color: var(--danger);">*</span></label>
                        <input type="number" id="credits" name="credits" class="form-control" placeholder="3" min="1" max="6" value="{{ old('credits', 3) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="lecturer_name" class="form-label">Nama Dosen Pengampu</label>
                        <input type="text" id="lecturer_name" name="lecturer_name" class="form-control" placeholder="Dr. Edy Mulyanto, S.Si, M.Kom" value="{{ old('lecturer_name') }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="schedule_day" class="form-label">Hari Perkuliahan <span style="color: var(--danger);">*</span></label>
                        <select id="schedule_day" name="schedule_day" class="form-control" required>
                            <option value="">-- Pilih Hari --</option>
                            <option value="0" {{ old('schedule_day') === '0' ? 'selected' : '' }}>Senin</option>
                            <option value="1" {{ old('schedule_day') === '1' ? 'selected' : '' }}>Selasa</option>
                            <option value="2" {{ old('schedule_day') === '2' ? 'selected' : '' }}>Rabu</option>
                            <option value="3" {{ old('schedule_day') === '3' ? 'selected' : '' }}>Kamis</option>
                            <option value="4" {{ old('schedule_day') === '4' ? 'selected' : '' }}>Jumat</option>
                            <option value="5" {{ old('schedule_day') === '5' ? 'selected' : '' }}>Sabtu</option>
                            <option value="6" {{ old('schedule_day') === '6' ? 'selected' : '' }}>Minggu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="room" class="form-label">Ruangan</label>
                        <input type="text" id="room" name="room" class="form-control" placeholder="Lab Komputer 2" value="{{ old('room') }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="schedule_start" class="form-label">Jam Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="time" id="schedule_start" name="schedule_start" class="form-control" value="{{ old('schedule_start', '08:00') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule_end" class="form-label">Jam Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="time" id="schedule_end" name="schedule_end" class="form-control" value="{{ old('schedule_end', '10:30') }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="semester" class="form-label">Semester Akademik <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="semester" name="semester" class="form-control" placeholder="e.g. 2025/2026-Genap" value="{{ old('semester', '2025/2026-Genap') }}" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" checked>
                        Mata Kuliah Aktif
                    </label>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary">Simpan Mata Kuliah</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">Batalkan</a>
                </div>

            </form>
        </div>
    </div>

@endsection
