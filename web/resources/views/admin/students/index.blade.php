@extends('layouts.admin')

@section('title', 'Data Mahasiswa')
@section('header-title', 'Data Mahasiswa')
@section('header-subtitle', 'Kelola informasi mahasiswa dan data biometrik wajah')

@push('styles')
<style>
/* ── Mobile Card Layout ─────────────────────────────── */
.student-cards { display: none; flex-direction: column; gap: 14px; }

.student-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
}
.student-card:hover { box-shadow: var(--shadow-md); }

.student-card-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.student-card-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary-light);
    flex-shrink: 0;
}

.student-card-info { flex: 1; min-width: 0; }

.student-card-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text-main);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.student-card-nim {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(27,42,107,0.08);
    padding: 2px 8px;
    border-radius: 5px;
    display: inline-block;
}

.student-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.student-card-meta span { display: flex; align-items: center; gap: 5px; }
.student-card-meta i { color: var(--primary); width: 14px; }

.student-card-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.student-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
}
.student-card-actions .btn { flex: 1; min-width: 0; font-size: 0.78rem; padding: 7px 10px; justify-content: center; }

@media (max-width: 768px) {
    .table-responsive { display: none !important; }
    .student-cards { display: flex; }

    /* Filter area stacked */
    .card-header form { flex-direction: column; }
    .card-header form .form-control,
    .card-header form .btn { max-width: 100% !important; width: 100%; }
    .card-header { flex-direction: column; }
    .card-header > a { width: 100%; justify-content: center; }
}
</style>
@endpush

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

        <!-- Desktop: Students Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Program Studi</th>
                            <th>Biometrik Wajah</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <img src="{{ $student->photo_url }}" alt="Profile" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--primary-light);">
                                </td>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $student->student_number }}</td>
                                <td style="font-weight: 600;">{{ $student->name }}</td>
                                <td>{{ $student->email ?: '-' }}</td>
                                <td>{{ $student->program_study ?: '-' }}</td>
                                <td>
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

            <!-- Mobile: Card Layout -->
            <div class="student-cards" style="padding: 14px;">
                @forelse($students as $student)
                    <div class="student-card">
                        <div class="student-card-top">
                            <img src="{{ $student->photo_url }}" alt="Profile" class="student-card-avatar">
                            <div class="student-card-info">
                                <div class="student-card-name">{{ $student->name }}</div>
                                <span class="student-card-nim">{{ $student->student_number }}</span>
                            </div>
                        </div>

                        <div class="student-card-meta">
                            <span><i class="fa-solid fa-graduation-cap"></i> {{ $student->program_study ?: 'Prodi -' }}</span>
                            <span><i class="fa-solid fa-envelope"></i> {{ $student->email ? \Str::limit($student->email, 18) : '-' }}</span>
                        </div>

                        <div class="student-card-badges">
                            @if($student->face_embeddings_count > 0)
                                <span class="badge badge-present" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-face-smile" style="margin-right: 4px;"></i>TERDAFTAR
                                </span>
                            @else
                                <span class="badge badge-rejected" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-face-meh" style="margin-right: 4px;"></i>KOSONG
                                </span>
                            @endif

                            @if($student->is_active)
                                <span class="badge badge-active">AKTIF</span>
                            @else
                                <span class="badge badge-inactive">NON-AKTIF</span>
                            @endif
                        </div>

                        <div class="student-card-actions">
                            <a href="{{ route('admin.students.face', $student->id) }}" class="btn btn-secondary btn-sm" style="color: var(--accent); border-color: rgba(245,166,35,0.3);">
                                <i class="fa-solid fa-face-viewfinder"></i>
                                <span>Wajah</span>
                            </a>
                            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Hapus mahasiswa ini?')" style="flex:1; min-width:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.2); width:100%; justify-content:center;">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Tidak ada data mahasiswa ditemukan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $students->links() }}
    </div>

@endsection
