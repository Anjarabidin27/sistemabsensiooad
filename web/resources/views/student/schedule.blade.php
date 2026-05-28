@extends('layouts.student')

@section('title', 'Jadwal Kuliah')

@section('content')
    <!-- Top Header -->
    <header class="student-header" style="padding-bottom: 30px;">
        <div class="student-header-top" style="margin-bottom: 0;">
            <a href="{{ route('student.home') }}" style="color: white; font-size: 1.1rem;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 1.05rem;">Jadwal Kuliah</span>
            <div style="width: 20px;"></div> <!-- Spacer -->
        </div>
    </header>

    <div class="student-body">
        
        <div class="card">
            <div class="card-header">
                <span class="card-title">Daftar Jadwal Kuliah Semester</span>
            </div>
            
            <div class="card-body" style="padding: 0 16px;">
                @if($courses->isEmpty())
                    <div style="padding: 40px 10px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-calendar-minus" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                        <span>Belum ada mata kuliah yang terdaftar.</span>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @php
                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        @endphp
                        
                        @foreach($days as $dayIndex => $dayName)
                            @php
                                $dayCourses = $courses->where('schedule_day', $dayIndex);
                            @endphp
                            
                            @if($dayCourses->isNotEmpty())
                                <div style="margin-top: 16px; margin-bottom: 4px; padding-bottom: 4px; border-bottom: 2px solid var(--accent); display: flex; align-items: center; gap: 8px;">
                                    <span style="background-color: var(--primary); color: white; width: 8px; height: 18px; border-radius: 4px;"></span>
                                    <h4 style="font-weight: 800; font-size: 0.95rem; color: var(--primary-dark);">{{ $dayName }}</h4>
                                </div>
                                
                                @foreach($dayCourses as $course)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--border-color);">
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--primary-dark);">{{ $course->name }}</span>
                                            <span style="font-size: 0.725rem; color: var(--text-muted);">
                                                {{ $course->code }} · {{ $course->credits }} SKS
                                            </span>
                                            <span style="font-size: 0.725rem; color: var(--text-muted); font-weight: 500;">
                                                Lecturer: {{ $course->lecturer_name ?: '-' }}
                                            </span>
                                        </div>
                                        <div style="text-align: right; display: flex; flex-direction: column; gap: 2px;">
                                            <span style="font-weight: 700; font-size: 0.8rem; color: var(--primary);">
                                                {{ substr($course->schedule_start, 0, 5) }} - {{ substr($course->schedule_end, 0, 5) }}
                                            </span>
                                            <span style="font-size: 0.7rem; color: var(--text-muted);">
                                                Ruang: {{ $course->room ?: '-' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
