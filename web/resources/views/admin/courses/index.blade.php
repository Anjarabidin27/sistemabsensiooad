@extends('layouts.admin')

@section('title', 'Data Mata Kuliah')
@section('header-title', 'Data Mata Kuliah')
@section('header-subtitle', 'Kelola jadwal perkuliahan, SKS, ruangan, dan dosen pengampu')

@section('content')

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card">
        <!-- Search & Filter Header -->
        <div class="card-header" style="flex-wrap: wrap; gap: 16px; padding: 18px 24px;">
            <form action="{{ route('admin.courses.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
                <input type="text" name="search" class="form-control" placeholder="Cari Kode, Nama, Dosen..." style="max-width: 250px;" value="{{ request('search') }}">
                
                <select name="day" class="form-control" style="max-width: 150px;">
                    <option value="">-- Semua Hari --</option>
                    @php
                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    @endphp
                    @foreach($days as $idx => $dName)
                        <option value="{{ $idx }}" {{ request('day') !== null && request('day') == $idx ? 'selected' : '' }}>{{ $dName }}</option>
                    @endforeach
                </select>

                <select name="semester" class="form-control" style="max-width: 200px;">
                    <option value="">-- Semua Semester --</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Filter</span>
                </button>
                @if(request('search') || request('day') !== null || request('semester'))
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary" style="background-color: var(--border-color);">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Mata Kuliah</span>
            </a>
        </div>

        <!-- Courses Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Jadwal Kuliah</th>
                            <th>Ruangan</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $course->code }}</td>
                                <td style="font-weight: 600;">{{ $course->name }}</td>
                                <td>{{ $course->credits }}</td>
                                <td>{{ $course->lecturer_name ?: '-' }}</td>
                                <td style="font-weight: 600; color: var(--primary);">
                                    {{ $course->day_name }}, {{ substr($course->schedule_start, 0, 5) }} - {{ substr($course->schedule_end, 0, 5) }} WIB
                                </td>
                                <td>{{ $course->room ?: '-' }}</td>
                                <td>{{ $course->semester ?: '-' }}</td>
                                <td>
                                    @if($course->is_active)
                                        <span class="badge badge-active">AKTIF</span>
                                    @else
                                        <span class="badge badge-inactive">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.2);" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Tidak ada data mata kuliah ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $courses->links() }}
    </div>

@endsection
