document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const courseSelect = document.getElementById('course_id');
    const captureBtn = document.getElementById('capture-btn');
    const switchCameraBtn = document.getElementById('switch-camera-btn');
    const fallbackUpload = document.getElementById('fallback-upload');
    const viewfinder = document.getElementById('viewfinder');
    const overlay = document.getElementById('overlay');
    const feedbackBox = document.getElementById('feedback-box');
    const feedbackIcon = document.getElementById('feedback-icon');
    const feedbackText = document.getElementById('feedback-text');
    const instructions = document.getElementById('instructions');

    let stream = null;
    let useFacingMode = 'user'; // default front camera
    let hasMultipleCameras = false;

    // ── 1. Enable verify button when camera is ready ───────────────────
    function updateButtonState() {
        if (stream) {
            captureBtn.disabled = false;
        } else {
            captureBtn.disabled = true;
        }
    }

    // ── 2. Webcam connection logic ──────────────────────────────────
    async function startCamera() {
        if (stream) {
            stopCamera();
        }

        const constraints = {
            video: {
                facingMode: useFacingMode,
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            
            // Check if multiple video inputs are available
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            if (videoDevices.length > 1) {
                switchCameraBtn.style.display = 'block';
            }
            
            updateButtonState();
            
            // Reset overlay classes
            overlay.className = 'scanner-overlay';
        } catch (err) {
            console.error('Error accessing webcam:', err);
            showFeedback('error', 'Gagal mengakses kamera. Silakan periksa izin kamera browser Anda.');
            overlay.classList.add('error');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        updateButtonState();
    }

    // Initialize camera
    startCamera();

    // Switch Camera Button (Front/Back)
    switchCameraBtn.addEventListener('click', function () {
        useFacingMode = useFacingMode === 'user' ? 'environment' : 'user';
        startCamera();
    });

    // ── 3. Capture & Verify Wajah ────────────────────────────────────
    captureBtn.addEventListener('click', async function () {
        const courseId = courseSelect.value;
        if (!courseId) {
            showFeedback('error', 'Pilih mata kuliah terlebih dahulu.');
            return;
        }

        // Trigger camera flash effect
        const flash = document.createElement('div');
        flash.className = 'camera-flash flash-active';
        viewfinder.appendChild(flash);
        setTimeout(() => {
            flash.classList.remove('flash-active');
            setTimeout(() => flash.remove(), 200);
        }, 50);

        // Pause video feed to freeze-frame
        video.pause();

        // Set scanner active UI animation
        overlay.className = 'scanner-overlay active';
        feedbackBox.style.display = 'none';
        captureBtn.disabled = true;
        courseSelect.disabled = true;
        instructions.innerText = 'Sedang memproses pengenalan wajah... Mohon tidak bergerak.';

        // Capture frame to canvas
        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        
        // Mirror the image on canvas (since video is mirrored)
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Reset transform
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        const base64Image = canvas.toDataURL('image/jpeg', 0.9);

        // Submit via AJAX
        submitScan(courseId, base64Image);
    });

    // ── 4. Fallback File Upload ─────────────────────────────────────
    fallbackUpload.addEventListener('change', function (e) {
        const courseId = courseSelect.value;
        if (!courseId) {
            showFeedback('error', 'Pilih mata kuliah terlebih dahulu sebelum mengunggah foto.');
            fallbackUpload.value = '';
            return;
        }

        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const base64Image = event.target.result;
            
            // Set scanning state
            overlay.className = 'scanner-overlay active';
            feedbackBox.style.display = 'none';
            captureBtn.disabled = true;
            courseSelect.disabled = true;
            instructions.innerText = 'Memproses unggahan foto...';

            submitScan(courseId, base64Image);
        };
        reader.readAsDataURL(file);
    });

    // ── 5. Ajax Submit ──────────────────────────────────────────────
    async function submitScan(courseId, base64Image) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch('/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    course_id: courseId,
                    image_base64: base64Image
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                stopCamera();
                overlay.className = 'scanner-overlay';

                // Get modal elements
                const modal = document.getElementById('attendance-modal');
                const modalIcon = document.getElementById('modal-icon');
                const modalTitle = document.getElementById('modal-title');
                const modalBody = document.getElementById('modal-body');
                const modalCloseBtn = document.getElementById('modal-close-btn');

                // Customize modal design based on on-time vs late status
                if (data.attendance_status === 'present') {
                    modalIcon.className = 'modal-icon success';
                    modalIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                    modalTitle.innerText = 'Presensi Berhasil!';
                } else {
                    modalIcon.className = 'modal-icon late';
                    modalIcon.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
                    modalTitle.innerText = 'Presensi Terlambat';
                }

                modalBody.innerHTML = data.popup_message;
                modal.style.display = 'flex';

                // Redirect handler
                modalCloseBtn.addEventListener('click', function () {
                    window.location.href = '/home';
                });

                // Auto redirect after 10 seconds fallback
                setTimeout(() => {
                    window.location.href = '/home';
                }, 10000);
            } else if (data.status === 'info') {
                overlay.className = 'scanner-overlay';
                showFeedback('success', data.message); // styled green
                instructions.innerText = 'Pengalihan kembali ke beranda...';
                stopCamera();
                setTimeout(() => {
                    window.location.href = '/home';
                }, 3000);
            } else {
                // Resume camera stream since recognition failed
                video.play();

                // Error (recognition failed, database error, mismatch student, etc)
                overlay.className = 'scanner-overlay error';
                showFeedback('error', data.message || 'Gagal memverifikasi wajah.');
                
                // Re-enable interface
                captureBtn.disabled = false;
                courseSelect.disabled = false;
                instructions.innerText = 'Posisikan wajah Anda kembali dan ketuk Verifikasi.';
            }
        } catch (err) {
            console.error('AJAX Error:', err);
            
            // Resume camera stream since error occurred
            video.play();

            overlay.className = 'scanner-overlay error';
            showFeedback('error', 'Koneksi error. Hubungi admin atau periksa koneksi internet Anda.');
            
            // Re-enable interface
            captureBtn.disabled = false;
            courseSelect.disabled = false;
            instructions.innerText = 'Pilih kelas dan ketuk Ambil & Verifikasi.';
        }
    }

    // Helper to display feedback messages
    function showFeedback(type, message) {
        feedbackBox.style.display = 'flex';
        feedbackBox.className = `feedback-message feedback-${type}`;
        feedbackText.innerText = message;

        if (type === 'success') {
            feedbackIcon.className = 'fa-solid fa-circle-check';
        } else {
            feedbackIcon.className = 'fa-solid fa-circle-exclamation';
        }
    }
});
