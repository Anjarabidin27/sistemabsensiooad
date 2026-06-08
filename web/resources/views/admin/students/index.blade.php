@extends('layouts.admin')

@section('title', 'Data Mahasiswa')
@section('header-title', 'Data Mahasiswa')
@section('header-subtitle', 'Kelola informasi mahasiswa dan data biometrik wajah')

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
            <form action="{{ route('admin.students.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
                <input type="text" name="search" class="form-control" placeholder="Cari NIM, Nama, Email..." style="max-width: 250px;" value="{{ request('search') }}">
                
                <select name="program_study" class="form-control" style="max-width: 200px;">
                    <option value="">-- Semua Prodi --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog }}" {{ request('program_study') == $prog ? 'selected' : '' }}>{{ $prog }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-control" style="max-width: 150px;">
                    <option value="">-- Semua Status --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>

                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Filter</span>
                </button>
                @if(request('search') || request('program_study') || request('status') !== null)
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary" style="background-color: var(--border-color);">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Mahasiswa</span>
            </a>
        </div>

        <!-- Students Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="hide-mobile">Foto</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="hide-mobile">Email</th>
                            <th class="hide-mobile">Program Studi</th>
                            <th class="hide-mobile">Biometrik Wajah</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="hide-mobile">
                                    <img src="{{ $student->photo_url }}" alt="Profile" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--primary-light);">
                                </td>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $student->student_number }}</td>
                                <td style="font-weight: 600;">{{ $student->name }}</td>
                                <td class="hide-mobile">{{ $student->email ?: '-' }}</td>
                                <td class="hide-mobile">{{ $student->program_study ?: '-' }}</td>
                                <td class="hide-mobile">
                                    @if($student->face_embeddings_count > 0)
                                        <span class="badge badge-present" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-face-smile" style="margin-right: 4px;"></i>
                                            TERDAFTAR
                                        </span>
                                    @else
                                        <span class="badge badge-rejected" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-face-meh" style="margin-right: 4px;"></i>
                                            KOSONG
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->is_active)
                                        <span class="badge badge-active">AKTIF</span>
                                    @else
                                        <span class="badge badge-inactive">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="{{ route('admin.students.face', $student->id) }}" class="btn btn-secondary btn-sm" title="Kelola Wajah" style="color: var(--accent); border-color: rgba(245,166,35,0.3);">
                                            <i class="fa-solid fa-face-viewfinder"></i>
                                            <span>Wajah</span>
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini dan seluruh data wajahnya?')">
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
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Tidak ada data mahasiswa ditemukan.
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
        {{ $students->links() }}
    </div>

@endsection
