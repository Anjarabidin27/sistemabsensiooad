@extends('layouts.student')

@section('title', 'Riwayat Presensi')

@section('content')
    <!-- Top Header -->
    <header class="student-header" style="padding: 16px 20px; border-radius: 0 0 16px 16px;">
        <div class="student-header-top" style="margin-bottom: 0;">
            <a href="{{ route('student.home') }}" style="color: white; font-size: 1.1rem; padding: 4px; display: flex; align-items: center;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 1.05rem; letter-spacing: 0.5px;">Riwayat Kehadiran</span>
            <div style="width: 28px;"></div> <!-- Spacer to center the title -->
        </div>
    </header>

    <div class="student-body" style="margin-top: 0; padding-top: 16px;">
        <div class="student-history-wrapper">
            
            <!-- LEFT COLUMN: Stats & Filters -->
            <div class="student-history-sidebar">
                
                <!-- Quick Stats Sub-card -->
                <div class="card" style="margin-bottom: 16px; border-left: 4px solid var(--primary);">
                    <div class="card-body" style="padding: 14px;">
                        <span style="font-size: 0.725rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 10px;">Ringkasan Kehadiran</span>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; gap: 8px;">
                            <div style="background-color: var(--success-light); padding: 8px; border-radius: var(--radius-sm);">
                                <span style="font-size: 0.625rem; font-weight: 600; color: var(--text-muted); display: block;">Hadir</span>
                                <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--success); margin-top: 2px;">{{ $present }}</h4>
                            </div>
                            <div style="background-color: var(--warning-light); padding: 8px; border-radius: var(--radius-sm);">
                                <span style="font-size: 0.625rem; font-weight: 600; color: var(--text-muted); display: block;">Terlambat</span>
                                <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--warning); margin-top: 2px;">{{ $late }}</h4>
                            </div>
                            <div style="background-color: rgba(27, 42, 107, 0.05); padding: 8px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                <span style="font-size: 0.625rem; font-weight: 600; color: var(--text-muted); display: block;">Rasio</span>
                                <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--primary); margin-top: 2px;">{{ number_format($attendanceRate, 0) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header" style="padding: 12px 16px; background-color: rgba(27, 42, 107, 0.02);">
                        <span class="card-title" style="font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-filter" style="color: var(--accent);"></i> Filter Presensi
                        </span>
                        @if(request('course_id') || request('date') || request('status'))
                            <span style="background-color: var(--accent); width: 6px; height: 6px; border-radius: 50%;"></span>
                        @endif
                    </div>
                    <div class="card-body" style="padding: 16px;">
                        <form action="{{ route('student.history') }}" method="GET" style="display: flex; flex-direction: column; gap: 12px;">
                            
                            <!-- Filter Mata Kuliah -->
                            <div>
                                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 4px;">Mata Kuliah</label>
                                <select name="course_id" class="form-control" style="font-size: 0.8rem; padding: 8px 12px; border-radius: var(--radius-sm);">
                                    <option value="">-- Semua Mata Kuliah --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Tanggal -->
                            <div>
                                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 4px;">Tanggal</label>
                                <input type="date" name="date" class="form-control" style="font-size: 0.8rem; padding: 8px 12px; border-radius: var(--radius-sm);" value="{{ request('date') }}">
                            </div>

                            <!-- Filter Status -->
                            <div>
                                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 4px;">Status</label>
                                <select name="status" class="form-control" style="font-size: 0.8rem; padding: 8px 12px; border-radius: var(--radius-sm);">
                                    <option value="">-- Semua Status --</option>
                                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>

                            <!-- Submit and Reset Grid -->
                            <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-top: 6px;">
                                <button type="submit" class="btn btn-primary" style="font-size: 0.75rem; padding: 8px 14px; border-radius: var(--radius-sm); font-weight: 700; flex-grow: 1; justify-content: center;">
                                    <i class="fa-solid fa-magnifying-glass" style="font-size: 0.7rem;"></i> Terapkan
                                </button>
                                
                                @if(request('course_id') || request('date') || request('status'))
                                    <a href="{{ route('student.history') }}" class="btn btn-secondary" style="font-size: 0.75rem; padding: 8px 12px; border-radius: var(--radius-sm); border: 1.5px solid var(--border-color); color: var(--text-muted); display: flex; align-items: center; justify-content: center;" title="Reset Filter">
                                        <i class="fa-solid fa-rotate-right"></i>
                                    </a>
                                @endif
                            </div>

                        </form>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Attendance Log List -->
            <div class="student-history-content">
                
                <div class="card">
                    <div class="card-header" style="padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                        <span class="card-title" style="font-size: 0.9rem; font-weight: 700;">Riwayat Kehadiran Kelas</span>
                        @if(request('course_id') || request('date') || request('status'))
                            <span style="font-size: 0.7rem; color: var(--accent); font-weight: 600; background-color: rgba(245,166,35,0.1); padding: 2px 8px; border-radius: var(--radius-full);">
                                Filter Aktif
                            </span>
                        @endif
                    </div>
                    
                    <div class="card-body" style="padding: 0 16px;">
                        @if($attendances->isEmpty())
                            <div style="padding: 48px 16px; text-align: center; color: var(--text-muted);">
                                <div style="background-color: var(--bg-main); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: var(--text-muted); opacity: 0.7; border: 1px dashed var(--border-color);">
                                    <i class="fa-solid fa-clipboard-question" style="font-size: 1.75rem;"></i>
                                </div>
                                <span style="font-weight: 700; font-size: 0.875rem; display: block; color: var(--primary-dark); margin-bottom: 4px;">Tidak Ada Riwayat</span>
                                <p style="font-size: 0.75rem; color: var(--text-muted); max-width: 240px; margin: 0 auto 16px auto;">Tidak ditemukan log kehadiran yang sesuai dengan kriteria filter Anda.</p>
                                @if(request('course_id') || request('date') || request('status'))
                                    <a href="{{ route('student.history') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: var(--radius-sm);">
                                        <i class="fa-solid fa-rotate-right" style="margin-right: 4px;"></i> Reset Filter
                                    </a>
                                @endif
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach($attendances as $row)
                                    <div class="history-item" style="display: flex; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border-color); align-items: center; transition: var(--transition);">
                                        
                                        <!-- Captured Photo Thumbnail -->
                                        @if($row->image_path)
                                            <a href="{{ asset('storage/' . $row->image_path) }}" target="_blank" style="flex-shrink: 0;">
                                                <div class="thumbnail-wrapper" style="position: relative; overflow: hidden; border-radius: var(--radius-sm); border: 1.5px solid var(--border-color); width: 44px; height: 44px; box-shadow: var(--shadow-sm); transition: var(--transition);">
                                                    <img src="{{ asset('storage/' . $row->image_path) }}" alt="Snapshot" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                                </div>
                                            </a>
                                        @else
                                            <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background-color: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted); flex-shrink: 0; border: 1px solid var(--border-color);">
                                                <i class="fa-solid fa-image" style="font-size: 0.9rem;"></i>
                                            </div>
                                        @endif

                                        <!-- Info -->
                                        <div style="display: flex; flex-direction: column; gap: 3px; flex-grow: 1; min-width: 0;">
                                            <span style="font-weight: 700; font-size: 0.825rem; color: var(--primary-dark); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->course ? $row->course->name : 'Mata Kuliah Umum' }}">
                                                {{ $row->course ? $row->course->name : 'Mata Kuliah Umum' }}
                                            </span>
                                            
                                            <div style="display: flex; flex-wrap: wrap; gap: 4px 8px; align-items: center;">
                                                <span style="font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                                    <i class="fa-regular fa-calendar-days" style="color: var(--accent);"></i>
                                                    {{ $row->check_in_at->isoFormat('dddd, D MMMM Y') }}
                                                </span>
                                                <span style="font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                                    <i class="fa-regular fa-clock" style="color: var(--accent);"></i>
                                                    {{ $row->check_in_at->format('H:i') }} WIB
                                                </span>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 1px;">
                                                <span style="font-size: 0.65rem; color: var(--text-white); background-color: var(--primary-light); padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                                    AI Match: {{ $row->confidence_percent }}
                                                </span>
                                                @if($row->ip_address)
                                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-family: monospace;">
                                                        IP: {{ $row->ip_address }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        <div style="flex-shrink: 0;">
                                            {!! $row->status_badge !!}
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div style="padding: 12px 0; display: flex; justify-content: center;">
                                {{ $attendances->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
