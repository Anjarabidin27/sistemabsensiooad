@extends('layouts.student')

@section('title', 'Scan Presensi Wajah')

@section('styles')
    <style>
        .scanner-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        
        .feedback-message {
            display: none;
            width: 100%;
            border-radius: var(--radius-md);
            padding: 14px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .feedback-success {
            display: flex;
            background-color: var(--success-light);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .feedback-error {
            display: flex;
            background-color: var(--danger-light);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .scanner-instructions {
            font-size: 0.825rem;
            color: var(--text-muted);
            text-align: center;
            max-width: 320px;
            margin: 0 auto;
        }

        /* ── Pop-up Modal Style ────────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
            animation: fadeIn 0.25s ease-out;
        }

        .modal-card {
            background-color: var(--card-bg);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 380px;
            padding: 30px 24px;
            text-align: center;
            box-shadow: var(--shadow-lg);
            border-top: 5px solid var(--accent);
            transform: translateY(20px);
            animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .modal-icon {
            font-size: 3.5rem;
            margin-bottom: 16px;
        }
        .modal-icon.success {
            color: var(--success);
        }
        .modal-icon.late {
            color: var(--warning);
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 12px;
        }

        .modal-body {
            font-size: 0.9rem;
            color: var(--text-main);
            line-height: 1.6;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
@endsection

@section('content')
    <!-- Top Header -->
    <header class="student-header" style="padding-bottom: 30px;">
        <div class="student-header-top" style="margin-bottom: 0;">
            <a href="{{ route('student.home') }}" style="color: white; font-size: 1.1rem;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 1.05rem;">Presensi Wajah</span>
            <div style="width: 20px;"></div> <!-- Spacer -->
        </div>
    </header>

    <div class="student-body">
        
        <div class="card">
            <div class="card-body scanner-card">
                
                <!-- Class Selection -->
                <div class="form-group" style="width: 100%; margin-bottom: 8px;">
                    <label for="course_id" class="form-label">Pilih Mata Kuliah</label>
                    <select id="course_id" class="form-control" style="font-weight: 600; color: var(--primary);">
                        <option value="">-- Pilih Mata Kuliah Anda --</option>
                        @foreach($courses as $course)
                            @php
                                $isToday = ($course->schedule_day == (Carbon\Carbon::now()->dayOfWeekIso - 1));
                            @endphp
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }} [{{ $course->code }}] {{ $isToday ? '• Hari Ini' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Webcam viewfinder -->
                <div class="scanner-viewfinder" id="viewfinder">
                    <video id="video" class="scanner-video" autoplay playsinline muted></video>
                    
                    <!-- Scanner Overlays -->
                    <div class="scanner-overlay" id="overlay">
                        <div class="scanner-frame"></div>
                        <div class="scanner-scanline"></div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="scanner-instructions" id="instructions">
                    <p>Pilih kelas terlebih dahulu, kemudian posisikan wajah Anda di tengah bingkai oval dan tatap kamera.</p>
                </div>

                <!-- Feedback Message Container -->
                <div class="feedback-message" id="feedback-box">
                    <i class="fa-solid" id="feedback-icon"></i>
                    <span id="feedback-text"></span>
                </div>

                <!-- Controls -->
                <div style="width: 100%; display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                    <button id="capture-btn" class="btn btn-primary btn-full" disabled>
                        <i class="fa-solid fa-camera"></i>
                        <span>Ambil & Verifikasi Kehadiran</span>
                    </button>
                    
                    <button id="switch-camera-btn" class="btn btn-secondary btn-full btn-sm" style="display: none;">
                        <i class="fa-solid fa-camera-rotate"></i>
                        <span>Ganti Kamera</span>
                    </button>
                </div>

                <!-- Fallback file upload -->
                <div style="margin-top: 10px; text-align: center; width: 100%;">
                    <span style="font-size: 0.775rem; color: var(--text-muted);">Kamera bermasalah? </span>
                    <label style="font-size: 0.775rem; font-weight: 700; color: var(--accent); cursor: pointer; text-decoration: underline;">
                        Upload Foto Manual
                        <input type="file" id="fallback-upload" accept="image/*" style="display: none;">
                    </label>
                </div>

            </div>
        </div>

    </div>

    <!-- Hidden Canvas for frame extraction -->
    <canvas id="canvas" style="display: none;"></canvas>

    <!-- Success/Late Popup Modal -->
    <div id="attendance-modal" class="modal-overlay" style="display: none;">
        <div class="modal-card" id="modal-card">
            <div class="modal-icon" id="modal-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 class="modal-title" id="modal-title">Presensi Sukses!</h2>
            <div class="modal-body" id="modal-body">
                <!-- Dynamic Message -->
            </div>
            <button id="modal-close-btn" class="btn btn-primary" style="margin-top: 24px; width: 100%;">
                Kembali ke Beranda
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include Webcam Controller scripts -->
    <script src="{{ asset('js/webcam.js') }}"></script>
@endsection
