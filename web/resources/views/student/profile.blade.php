@extends('layouts.student')

@section('title', 'Profil Mahasiswa')

@section('content')
    <!-- Top Header (Compact & Consistent Row) -->
    <header class="student-header" style="padding: 16px 20px; border-radius: 0 0 16px 16px;">
        <div class="student-header-top" style="margin-bottom: 0; display: flex; align-items: center; justify-content: space-between;">
            <a href="{{ route('student.home') }}" style="color: white; font-size: 1rem; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background-color: rgba(255,255,255,0.15); transition: var(--transition);">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 1.05rem; color: white;">Profil Saya</span>
            <div style="width: 34px;"></div> <!-- Spacer for perfect centering -->
        </div>
    </header>

    <div class="student-body" style="margin-top: 0; padding-top: 16px;">
        
        <!-- Status & Flash Message Alert -->
        @if(session('success'))
            <div class="alert alert-success" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                <i class="fa-solid fa-circle-check" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
        
        <!-- Profile Card -->
        <div class="card" style="text-align: center; padding: 28px 0; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div class="card-body" style="display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 0 24px;">
                <!-- Profile Image with Camera Upload Overlay & Spinner -->
                <div style="position: relative; width: 106px; height: 106px; margin: 0 auto;">
                    <!-- Loading Spinner Overlay -->
                    <div id="upload-spinner" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; background-color: rgba(15, 26, 74, 0.65); display: none; align-items: center; justify-content: center; z-index: 10;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent); font-size: 1.75rem;"></i>
                    </div>
                    
                    <img src="{{ $student->photo_url }}" alt="Profile Photo" style="width: 106px; height: 106px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-light); box-shadow: var(--shadow-md); transition: var(--transition);">
                    
                    <label for="profile_photo_input" style="position: absolute; bottom: 0; right: 0; background-color: var(--accent); color: var(--primary-dark); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; cursor: pointer; box-shadow: var(--shadow-md); transition: var(--transition);" onmouseover="this.style.transform='scale(1.15)';" onmouseout="this.style.transform='scale(1)';">
                        <i class="fa-solid fa-camera" style="font-size: 0.95rem;"></i>
                    </label>
                </div>
                
                <form id="profile-photo-form" action="{{ route('student.profile.upload') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    <input type="file" id="profile_photo_input" name="profile_photo" accept="image/*" onchange="handlePhotoUpload();">
                </form>
                
                <div>
                    <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--primary-dark);">{{ $student->name }}</h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">NIM: {{ $student->student_number }}</p>
                </div>

                @if($student->faceEmbeddings()->exists())
                    <span class="badge badge-present" style="font-size: 0.725rem; display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: var(--radius-full);">
                        <i class="fa-solid fa-circle-check"></i>
                        Wajah Terdaftar
                    </span>
                @else
                    <span class="badge badge-rejected" style="font-size: 0.725rem; display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: var(--radius-full);">
                        <i class="fa-solid fa-circle-xmark"></i>
                        Wajah Belum Terdaftar
                    </span>
                @endif
            </div>
        </div>

        <!-- Academic Info -->
        <div class="card" style="border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div class="card-header" style="padding: 16px 20px; background-color: rgba(27, 42, 107, 0.02);">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: var(--primary);">Informasi Akademik</span>
            </div>
            <div class="card-body" style="padding: 12px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Program Studi</span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $student->program_study ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Fakultas</span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $student->faculty ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Tahun Angkatan</span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $student->enrollment_year ?: '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Email Kampus</span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main); word-break: break-all; text-align: right; margin-left: 10px;">{{ $student->email ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div style="margin-top: 8px;">
            <form action="{{ route('student.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-full" style="padding: 12px; font-size: 0.9rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i>
                    Keluar Akun
                </button>
            </form>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function handlePhotoUpload() {
            const input = document.getElementById('profile_photo_input');
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            
            // Show spinner
            document.getElementById('upload-spinner').style.display = 'flex';

            // Compress using Canvas client-side to save bandwidth & make upload near-instant
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function (event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function () {
                    // Set max dimensions
                    const max_size = 400;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > max_size) {
                            height *= max_size / width;
                            width = max_size;
                        }
                    } else {
                        if (height > max_size) {
                            width *= max_size / height;
                            height = max_size;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert canvas to compressed JPEG blob (80% quality)
                    canvas.toBlob(function (blob) {
                        const formData = new FormData();
                        // Append compressed JPEG
                        formData.append('profile_photo', blob, 'profile.jpg');
                        // Append CSRF token
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                        // Upload via AJAX
                        fetch("{{ route('student.profile.upload') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            document.getElementById('upload-spinner').style.display = 'none';
                            // Reset file input value
                            input.value = '';
                            
                            if (data.status === 'success') {
                                // Update avatar src with timestamp to bust browser cache
                                const avatarImg = document.querySelector('img[alt="Profile Photo"]');
                                avatarImg.src = data.photo_url + '?t=' + new Date().getTime();
                                
                                showAlert('success', data.message);
                            } else {
                                showAlert('danger', data.message || 'Gagal mengunggah foto.');
                            }
                        })
                        .catch(error => {
                            document.getElementById('upload-spinner').style.display = 'none';
                            input.value = '';
                            showAlert('danger', 'Gagal mengunggah foto. Pastikan ukuran file tidak melebihi batas.');
                            console.error('Upload error:', error);
                        });
                    }, 'image/jpeg', 0.80);
                };
            };
        }

        function showAlert(type, message) {
            // Remove existing alert messages
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            // Build new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.boxShadow = 'var(--shadow-sm)';
            alertDiv.style.borderRadius = 'var(--radius-md)';
            
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-triangle-exclamation';
            icon.style.fontSize = '1.1rem';
            icon.style.flexShrink = '0';

            const text = document.createElement('span');
            text.innerText = message;

            alertDiv.appendChild(icon);
            alertDiv.appendChild(text);

            // Prepend alert at the top of the body
            const body = document.querySelector('.student-body');
            if (body) {
                body.insertBefore(alertDiv, body.firstChild);
                // Scroll alert into view smoothly
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    </script>
@endsection

