@extends('layouts.admin')

@section('title', 'Manajemen Biometrik Wajah')
@section('header-title', 'Registrasi Biometrik Wajah')
@section('header-subtitle', 'Pendaftaran wajah mahasiswa ke dalam database pengenalan AI')

@section('styles')
    <style>
        .face-panel {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .face-panel {
                grid-template-columns: 1fr;
            }
        }

        .registered-photo-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 30px 20px;
            text-align: center;
        }

        .registered-photo-img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .scanner-controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .progress-indicator {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')

    <!-- Flash message alerts -->
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

    <div class="face-panel">
        
        <!-- Left Side: Student Info & Current Face Info -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Profil Mahasiswa</span>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                        <img src="{{ $student->photo_url }}" alt="Profile" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                        <div>
                            <h3 style="font-weight: 800; font-size: 1.1rem; color: var(--primary-dark);">{{ $student->name }}</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">NIM: {{ $student->student_number }}</p>
                            <p style="font-size: 0.775rem; color: var(--text-muted);">{{ $student->program_study }} · {{ $student->faculty }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Face Embedding info card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Status Biometrik Wajah</span>
                </div>
                <div class="card-body">
                    @if($student->faceEmbeddings->isNotEmpty())
                        <div class="registered-photo-card">
                            @php
                                $embedding = $student->faceEmbeddings->first();
                                $registeredPhoto = $embedding->photo_path ?: $student->photo_path;
                            @endphp
                            
                            @if($registeredPhoto)
                                <img src="{{ asset('storage/' . $registeredPhoto) }}" alt="Registered Face" class="registered-photo-img">
                            @else
                                <div style="width: 200px; height: 200px; border-radius: 50%; background-color: var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 4rem; color: var(--text-muted);">
                                    <i class="fa-solid fa-face-smile"></i>
                                </div>
                            @endif

                            <div>
                                <span class="badge badge-present" style="margin-bottom: 8px;">TERDAFTAR</span>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">Terdaftar menggunakan model: <strong>{{ $embedding->model_used }}</strong></p>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Terdaftar pada: {{ $embedding->created_at ? $embedding->created_at->isoFormat('D MMM Y, HH:mm') : '-' }} WIB</p>
                            </div>

                            <form action="{{ route('admin.students.face.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data wajah mahasiswa ini? Tindakan ini akan menghapus data biometrik dari database AI.')" style="width: 100%;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-full">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus Data Wajah</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div style="padding: 30px 10px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-face-meh-blank" style="font-size: 3rem; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                            <h4 style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Wajah Belum Terdaftar</h4>
                            <p style="font-size: 0.8rem; margin-top: 4px;">Gunakan panel kamera di sebelah kanan untuk mendaftarkan wajah mahasiswa ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Webcam Camera Registration Panel -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Kamera Registrasi Wajah</span>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Ambil foto langsung atau unggah file</span>
            </div>
            
            <div class="card-body" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                
                <!-- Viewfinder -->
                <div class="scanner-viewfinder" id="viewfinder" style="max-width: 440px; aspect-ratio: 4/3;">
                    <video id="video" class="scanner-video" autoplay playsinline muted></video>
                    
                    <div class="scanner-overlay" id="overlay">
                        <div class="scanner-frame"></div>
                        <div class="scanner-scanline"></div>
                    </div>
                </div>

                <!-- Custom feedback response box -->
                <div id="feedback-alert" class="alert" style="display: none; width: 100%; margin-bottom: 0;"></div>

                <!-- Loader progress indicator -->
                <div class="progress-indicator" id="loader-box">
                    <div class="loader-spinner"></div>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--primary);" id="loader-text">Mengirim ke AI Engine...</span>
                </div>

                <!-- Controls -->
                <div class="scanner-controls">
                    <button id="capture-btn" class="btn btn-accent btn-full">
                        <i class="fa-solid fa-camera"></i>
                        <span>Ambil & Daftarkan Wajah</span>
                    </button>
                    
                    <button id="switch-camera-btn" class="btn btn-secondary btn-full btn-sm" style="display: none;">
                        <i class="fa-solid fa-camera-rotate"></i>
                        <span>Ganti Kamera</span>
                    </button>
                </div>

                <!-- File upload fallback -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 16px; width: 100%; text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Atau upload file foto wajah yang jelas:</span>
                    <div style="margin-top: 8px;">
                        <input type="file" id="photo-file" accept="image/*" class="form-control" style="padding: 6px 12px; font-size: 0.8rem;">
                    </div>
                    <button id="upload-btn" class="btn btn-secondary btn-sm" style="margin-top: 10px; width: 120px;">
                        Upload File
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Hidden Canvas -->
    <canvas id="canvas" style="display: none;"></canvas>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const captureBtn = document.getElementById('capture-btn');
            const switchCameraBtn = document.getElementById('switch-camera-btn');
            const photoFile = document.getElementById('photo-file');
            const uploadBtn = document.getElementById('upload-btn');
            
            const overlay = document.getElementById('overlay');
            const feedbackAlert = document.getElementById('feedback-alert');
            const loaderBox = document.getElementById('loader-box');
            const loaderText = document.getElementById('loader-text');

            let stream = null;
            let facingMode = 'user';

            // ── 1. Start Camera stream ──────────────────────────────────────
            async function initCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: facingMode,
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        },
                        audio: false
                    });
                    video.srcObject = stream;
                    
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                    if (videoDevices.length > 1) {
                        switchCameraBtn.style.display = 'block';
                    }
                } catch (err) {
                    console.error('Error starting camera:', err);
                    showFeedback('danger', '<i class="fa-solid fa-triangle-exclamation"></i> Gagal mengakses kamera. Gunakan upload file sebagai alternatif.');
                }
            }

            initCamera();

            switchCameraBtn.addEventListener('click', function () {
                facingMode = facingMode === 'user' ? 'environment' : 'user';
                initCamera();
            });

            // ── 2. Capture and Send ─────────────────────────────────────────
            captureBtn.addEventListener('click', function () {
                if (!stream) {
                    showFeedback('danger', '<i class="fa-solid fa-triangle-exclamation"></i> Kamera tidak aktif.');
                    return;
                }

                // Trigger camera flash effect
                const viewfinder = document.getElementById('viewfinder');
                const flash = document.createElement('div');
                flash.className = 'camera-flash flash-active';
                viewfinder.appendChild(flash);
                setTimeout(() => {
                    flash.classList.remove('flash-active');
                    setTimeout(() => flash.remove(), 200);
                }, 50);

                // Pause video feed to freeze-frame
                video.pause();

                // Snap frame to canvas
                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                // Mirror canvas drawing
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                ctx.setTransform(1, 0, 0, 1, 0, 0);

                const base64Image = canvas.toDataURL('image/jpeg', 0.95);
                
                // Submit base64
                sendRegistration({ image_base64: base64Image });
            });

            // ── 3. File Upload Submit ────────────────────────────────────────
            uploadBtn.addEventListener('click', function () {
                const file = photoFile.files[0];
                if (!file) {
                    showFeedback('danger', '<i class="fa-solid fa-triangle-exclamation"></i> Pilih file foto terlebih dahulu.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const base64Image = e.target.result;
                    sendRegistration({ image_base64: base64Image });
                };
                reader.readAsDataURL(file);
            });

            // ── 4. Send Registration AJAX ──────────────────────────────────
            async function sendRegistration(payload) {
                // UI state reset
                feedbackAlert.style.display = 'none';
                loaderBox.style.display = 'flex';
                loaderText.innerText = 'Mengirim ke AI Engine...';
                overlay.className = 'scanner-overlay active';
                captureBtn.disabled = true;
                uploadBtn.disabled = true;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch('{{ route("admin.students.face.register", $student->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    loaderBox.style.display = 'none';

                    if (data.status === 'success') {
                        overlay.className = 'scanner-overlay active';
                        showFeedback('success', '<i class="fa-solid fa-circle-check"></i> ' + data.message + ' Halaman akan direfresh...');
                        
                        // Stop camera track and refresh page
                        if (stream) {
                            stream.getTracks().forEach(t => t.stop());
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Resume video stream since registration failed
                        video.play();

                        // AI Engine / Matching registration error
                        overlay.className = 'scanner-overlay error';
                        showFeedback('danger', '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message);
                        captureBtn.disabled = false;
                        uploadBtn.disabled = false;
                    }
                } catch (err) {
                    console.error('Error registering:', err);
                    
                    // Resume video stream since registration failed
                    video.play();

                    loaderBox.style.display = 'none';
                    overlay.className = 'scanner-overlay error';
                    showFeedback('danger', '<i class="fa-solid fa-triangle-exclamation"></i> Koneksi error. Hubungi admin atau periksa status service Flask.');
                    captureBtn.disabled = false;
                    uploadBtn.disabled = false;
                }
            }

            function showFeedback(type, html) {
                feedbackAlert.style.display = 'block';
                feedbackAlert.className = `alert alert-${type}`;
                feedbackAlert.innerHTML = html;
            }
        });
    </script>
@endsection
