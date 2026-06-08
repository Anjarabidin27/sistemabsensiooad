/**
 * SIHADIR — Webcam Controller with Haversine Geolocation Verification
 *
 * Alur:
 * 1. User pilih mata kuliah
 * 2. Jika mata kuliah mewajibkan verifikasi GPS → ambil posisi user via Geolocation API
 * 3. Hitung jarak user ke koordinat ruang kelas dengan rumus Haversine
 * 4. Jika di luar radius → tolak scan sebelum kamera aktif lebih lanjut
 * 5. Jika lolos → ambil foto & kirim ke server (beserta koordinat user)
 */
document.addEventListener('DOMContentLoaded', function () {
    const video          = document.getElementById('video');
    const canvas         = document.getElementById('canvas');
    const courseSelect   = document.getElementById('course_id');
    const captureBtn     = document.getElementById('capture-btn');
    const switchCameraBtn= document.getElementById('switch-camera-btn');
    const fallbackUpload = document.getElementById('fallback-upload');
    const viewfinder     = document.getElementById('viewfinder');
    const overlay        = document.getElementById('overlay');
    const feedbackBox    = document.getElementById('feedback-box');
    const feedbackIcon   = document.getElementById('feedback-icon');
    const feedbackText   = document.getElementById('feedback-text');
    const instructions   = document.getElementById('instructions');
    const gpsStatusBox   = document.getElementById('gps-status-box');
    const gpsIcon        = document.getElementById('gps-icon');
    const gpsText        = document.getElementById('gps-text');

    let stream             = null;
    let useFacingMode      = 'user'; // default front camera
    let studentLat         = null;
    let studentLng         = null;
    let gpsCheckPassed     = true; // default: lolos jika tidak ada kewajiban GPS

    // ─────────────────────────────────────────────────────────────────────────
    // HAVERSINE ALGORITHM (Pure JavaScript)
    // Menghitung jarak antara dua koordinat GPS dalam meter
    // ─────────────────────────────────────────────────────────────────────────
    function haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6_371_000; // Radius bumi dalam meter
        const toRad = (deg) => deg * Math.PI / 180;

        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2
                + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // meter
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GPS STATUS BADGE
    // ─────────────────────────────────────────────────────────────────────────
    function showGpsStatus(type, message) {
        gpsStatusBox.style.display = 'flex';
        gpsText.textContent = message;

        // Reset style
        gpsStatusBox.style.background  = '';
        gpsStatusBox.style.color       = '';
        gpsStatusBox.style.border      = '';
        gpsIcon.className              = 'fa-solid fa-location-dot';

        if (type === 'checking') {
            gpsStatusBox.style.background = 'rgba(14,165,233,0.1)';
            gpsStatusBox.style.color      = 'var(--accent)';
            gpsStatusBox.style.border     = '1px solid rgba(14,165,233,0.3)';
            gpsIcon.className = 'fa-solid fa-spinner fa-spin';
        } else if (type === 'ok') {
            gpsStatusBox.style.background = 'rgba(16,185,129,0.1)';
            gpsStatusBox.style.color      = '#065f46';
            gpsStatusBox.style.border     = '1px solid rgba(16,185,129,0.3)';
            gpsIcon.className = 'fa-solid fa-circle-check';
        } else if (type === 'error') {
            gpsStatusBox.style.background = 'rgba(239,68,68,0.08)';
            gpsStatusBox.style.color      = '#991b1b';
            gpsStatusBox.style.border     = '1px solid rgba(239,68,68,0.25)';
            gpsIcon.className = 'fa-solid fa-circle-xmark';
        } else if (type === 'hidden') {
            gpsStatusBox.style.display = 'none';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GEOLOCATION: Ambil posisi mahasiswa & verifikasi Haversine
    // ─────────────────────────────────────────────────────────────────────────
    function checkStudentLocation(courseLat, courseLng, radius) {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject('Browser Anda tidak mendukung GPS. Hubungi admin.');
                return;
            }

            showGpsStatus('checking', 'Memeriksa lokasi GPS Anda...');
            captureBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    studentLat = position.coords.latitude;
                    studentLng = position.coords.longitude;

                    const distance = haversineDistance(studentLat, studentLng, courseLat, courseLng);
                    const distanceRounded = Math.round(distance);

                    if (distance <= radius) {
                        showGpsStatus('ok', `✓ Lokasi terverifikasi — Anda berada ${distanceRounded}m dari ruang kelas (batas: ${radius}m)`);
                        captureBtn.disabled = false;
                        resolve({ ok: true, distance: distanceRounded });
                    } else {
                        showGpsStatus('error', `✗ Di luar radius! Jarak Anda ${distanceRounded}m, batas radius ${radius}m. Harap pindah lebih dekat ke ruangan.`);
                        captureBtn.disabled = true;
                        reject(`Anda berada ${distanceRounded}m dari ruang kelas. Batas radius: ${radius}m.`);
                    }
                },
                (err) => {
                    let msg = 'Gagal mendapatkan lokasi GPS.';
                    if (err.code === 1) msg = 'Izin lokasi ditolak. Aktifkan izin GPS di browser Anda, lalu muat ulang halaman.';
                    if (err.code === 2) msg = 'Sinyal GPS tidak ditemukan. Pindah ke area dengan sinyal lebih baik.';
                    if (err.code === 3) msg = 'Waktu tunggu GPS habis. Coba lagi.';

                    showGpsStatus('error', msg);
                    captureBtn.disabled = true;
                    reject(msg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0 // Selalu ambil posisi terbaru
                }
            );
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COURSE SELECT CHANGE — cek GPS jika mata kuliah mewajibkan
    // ─────────────────────────────────────────────────────────────────────────
    async function onCourseChange() {
        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            showGpsStatus('hidden', '');
            gpsCheckPassed = true;
            studentLat = null;
            studentLng = null;
            updateButtonState();
            return;
        }

        const locationRequired = selectedOption.dataset.locationRequired === 'true';
        const courseLat        = parseFloat(selectedOption.dataset.latitude);
        const courseLng        = parseFloat(selectedOption.dataset.longitude);
        const radius           = parseInt(selectedOption.dataset.radius) || 100;

        if (!locationRequired || isNaN(courseLat) || isNaN(courseLng)) {
            // Tidak perlu verifikasi GPS untuk mata kuliah ini
            showGpsStatus('hidden', '');
            gpsCheckPassed = true;
            studentLat = null;
            studentLng = null;
            updateButtonState();
            return;
        }

        // Wajib GPS — mulai verifikasi lokasi
        gpsCheckPassed = false;
        updateButtonState();

        try {
            await checkStudentLocation(courseLat, courseLng, radius);
            gpsCheckPassed = true;
        } catch (errMsg) {
            gpsCheckPassed = false;
        }

        updateButtonState();
    }

    courseSelect.addEventListener('change', onCourseChange);
    // Jalankan juga saat halaman load (jika ada pre-selected course)
    onCourseChange();

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Tombol capture aktif hanya jika kamera ready DAN GPS check lolos
    // ─────────────────────────────────────────────────────────────────────────
    function updateButtonState() {
        captureBtn.disabled = !(stream && gpsCheckPassed);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Webcam connection logic
    // ─────────────────────────────────────────────────────────────────────────
    async function startCamera() {
        if (stream) stopCamera();

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

            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            if (videoDevices.length > 1) {
                switchCameraBtn.style.display = 'block';
            }

            updateButtonState();
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

    startCamera();

    switchCameraBtn.addEventListener('click', function () {
        useFacingMode = useFacingMode === 'user' ? 'environment' : 'user';
        startCamera();
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Capture & Verify Wajah (dengan validasi GPS terakhir)
    // ─────────────────────────────────────────────────────────────────────────
    captureBtn.addEventListener('click', async function () {
        const courseId = courseSelect.value;
        if (!courseId) {
            showFeedback('error', 'Pilih mata kuliah terlebih dahulu.');
            return;
        }

        // Double-check GPS sebelum foto diambil (re-verify untuk anti-bypass)
        const selectedOption    = courseSelect.options[courseSelect.selectedIndex];
        const locationRequired  = selectedOption.dataset.locationRequired === 'true';
        const courseLat         = parseFloat(selectedOption.dataset.latitude);
        const courseLng         = parseFloat(selectedOption.dataset.longitude);
        const radius            = parseInt(selectedOption.dataset.radius) || 100;

        if (locationRequired && !isNaN(courseLat) && !isNaN(courseLng)) {
            try {
                await checkStudentLocation(courseLat, courseLng, radius);
                gpsCheckPassed = true;
            } catch (errMsg) {
                showFeedback('error', errMsg);
                gpsCheckPassed = false;
                return; // Batalkan scan!
            }
        }

        // Trigger camera flash effect
        const flash = document.createElement('div');
        flash.className = 'camera-flash flash-active';
        viewfinder.appendChild(flash);
        setTimeout(() => {
            flash.classList.remove('flash-active');
            setTimeout(() => flash.remove(), 200);
        }, 50);

        video.pause();
        overlay.className = 'scanner-overlay active';
        feedbackBox.style.display = 'none';
        captureBtn.disabled = true;
        courseSelect.disabled = true;
        instructions.innerText = 'Sedang memproses pengenalan wajah... Mohon tidak bergerak.';

        const ctx = canvas.getContext('2d');
        canvas.width  = video.videoWidth  || 640;
        canvas.height = video.videoHeight || 480;

        // Mirror image (karena video di-mirror)
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        const base64Image = canvas.toDataURL('image/jpeg', 0.9);

        submitScan(courseId, base64Image, studentLat, studentLng);
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Fallback File Upload
    // ─────────────────────────────────────────────────────────────────────────
    fallbackUpload.addEventListener('change', async function (e) {
        const courseId = courseSelect.value;
        if (!courseId) {
            showFeedback('error', 'Pilih mata kuliah terlebih dahulu sebelum mengunggah foto.');
            fallbackUpload.value = '';
            return;
        }

        // Cek GPS juga untuk fallback upload
        const selectedOption    = courseSelect.options[courseSelect.selectedIndex];
        const locationRequired  = selectedOption.dataset.locationRequired === 'true';
        const courseLat         = parseFloat(selectedOption.dataset.latitude);
        const courseLng         = parseFloat(selectedOption.dataset.longitude);
        const radius            = parseInt(selectedOption.dataset.radius) || 100;

        if (locationRequired && !isNaN(courseLat) && !isNaN(courseLng)) {
            try {
                await checkStudentLocation(courseLat, courseLng, radius);
            } catch (errMsg) {
                showFeedback('error', errMsg);
                fallbackUpload.value = '';
                return;
            }
        }

        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const base64Image = event.target.result;

            overlay.className = 'scanner-overlay active';
            feedbackBox.style.display = 'none';
            captureBtn.disabled = true;
            courseSelect.disabled = true;
            instructions.innerText = 'Memproses unggahan foto...';

            submitScan(courseId, base64Image, studentLat, studentLng);
        };
        reader.readAsDataURL(file);
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Ajax Submit — kirim koordinat mahasiswa bersama scan
    // ─────────────────────────────────────────────────────────────────────────
    async function submitScan(courseId, base64Image, lat, lng) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const payload = {
            course_id:     courseId,
            image_base64:  base64Image,
        };

        // Sertakan koordinat mahasiswa jika tersedia
        if (lat !== null && lng !== null) {
            payload.student_lat = lat;
            payload.student_lng = lng;
        }

        try {
            const response = await fetch('/scan', {
                method: 'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.status === 'success') {
                stopCamera();
                overlay.className = 'scanner-overlay';

                const modal       = document.getElementById('attendance-modal');
                const modalIcon   = document.getElementById('modal-icon');
                const modalTitle  = document.getElementById('modal-title');
                const modalBody   = document.getElementById('modal-body');
                const modalClose  = document.getElementById('modal-close-btn');

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

                modalClose.addEventListener('click', () => {
                    window.location.href = '/home';
                });

                setTimeout(() => { window.location.href = '/home'; }, 10000);

            } else if (data.status === 'info') {
                overlay.className = 'scanner-overlay';
                showFeedback('success', data.message);
                instructions.innerText = 'Pengalihan kembali ke beranda...';
                stopCamera();
                setTimeout(() => { window.location.href = '/home'; }, 3000);

            } else {
                video.play();
                overlay.className = 'scanner-overlay error';
                showFeedback('error', data.message || 'Gagal memverifikasi wajah.');

                captureBtn.disabled  = false;
                courseSelect.disabled = false;
                instructions.innerText = 'Posisikan wajah Anda kembali dan ketuk Verifikasi.';
            }
        } catch (err) {
            console.error('AJAX Error:', err);
            video.play();
            overlay.className = 'scanner-overlay error';
            showFeedback('error', 'Koneksi error. Hubungi admin atau periksa koneksi internet Anda.');

            captureBtn.disabled  = false;
            courseSelect.disabled = false;
            instructions.innerText = 'Pilih kelas dan ketuk Ambil & Verifikasi.';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: tampilkan feedback message
    // ─────────────────────────────────────────────────────────────────────────
    function showFeedback(type, message) {
        feedbackBox.style.display = 'flex';
        feedbackBox.className     = `feedback-message feedback-${type}`;
        feedbackText.innerText    = message;

        feedbackIcon.className = (type === 'success')
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-exclamation';
    }
});
