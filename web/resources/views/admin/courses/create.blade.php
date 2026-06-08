@extends('layouts.admin')

@section('title', 'Tambah Mata Kuliah')
@section('header-title', 'Tambah Mata Kuliah')
@section('header-subtitle', 'Buat jadwal mata kuliah baru beserta slot ruangan perkuliahan')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #location-map { height: 300px; border-radius: 8px; border: 1px solid var(--border-color); }
    .location-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
        border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .location-badge.active { background: #dcfce7; color: #166534; }
    .location-badge.inactive { background: #fef9c3; color: #854d0e; }
</style>
@endpush

@section('content')

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <span class="card-title">Form Input Mata Kuliah</span>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
        
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <ul style="padding-left: 20px; font-size: 0.85rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.courses.store') }}" method="POST">
                @csrf
                
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="code" class="form-label">Kode Mata Kuliah <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" placeholder="A11.3503" value="{{ old('code') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Mata Kuliah <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Object Oriented Analysis and Design" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="credits" class="form-label">Jumlah SKS <span style="color: var(--danger);">*</span></label>
                        <input type="number" id="credits" name="credits" class="form-control" placeholder="3" min="1" max="6" value="{{ old('credits', 3) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="lecturer_name" class="form-label">Nama Dosen Pengampu</label>
                        <input type="text" id="lecturer_name" name="lecturer_name" class="form-control" placeholder="Dr. Edy Mulyanto, S.Si, M.Kom" value="{{ old('lecturer_name') }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="schedule_day" class="form-label">Hari Perkuliahan <span style="color: var(--danger);">*</span></label>
                        <select id="schedule_day" name="schedule_day" class="form-control" required>
                            <option value="">-- Pilih Hari --</option>
                            <option value="0" {{ old('schedule_day') === '0' ? 'selected' : '' }}>Senin</option>
                            <option value="1" {{ old('schedule_day') === '1' ? 'selected' : '' }}>Selasa</option>
                            <option value="2" {{ old('schedule_day') === '2' ? 'selected' : '' }}>Rabu</option>
                            <option value="3" {{ old('schedule_day') === '3' ? 'selected' : '' }}>Kamis</option>
                            <option value="4" {{ old('schedule_day') === '4' ? 'selected' : '' }}>Jumat</option>
                            <option value="5" {{ old('schedule_day') === '5' ? 'selected' : '' }}>Sabtu</option>
                            <option value="6" {{ old('schedule_day') === '6' ? 'selected' : '' }}>Minggu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="room" class="form-label">Ruangan</label>
                        <input type="text" id="room" name="room" class="form-control" placeholder="Lab Komputer 2" value="{{ old('room') }}">
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="schedule_start" class="form-label">Jam Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="time" id="schedule_start" name="schedule_start" class="form-control" value="{{ old('schedule_start', '08:00') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule_end" class="form-label">Jam Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="time" id="schedule_end" name="schedule_end" class="form-control" value="{{ old('schedule_end', '10:30') }}" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="semester" class="form-label">Semester Akademik <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="semester" name="semester" class="form-control" placeholder="e.g. 2025/2026-Genap" value="{{ old('semester', '2025/2026-Genap') }}" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" checked>
                        Mata Kuliah Aktif
                    </label>
                </div>

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: Konfigurasi Lokasi GPS (Haversine)    --}}
                {{-- ═══════════════════════════════════════════════ --}}
                <div style="border-top: 1px solid var(--border-color); margin-top: 24px; padding-top: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 700; margin: 0;"><i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 6px;"></i>Konfigurasi Lokasi Kelas (GPS)</h3>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 4px 0 0;">Jika diaktifkan, mahasiswa wajib berada dalam radius tertentu dari ruang kelas untuk bisa absen.</p>
                        </div>
                        <label id="loc-toggle-label" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; padding: 8px 14px; border-radius: 8px; border: 1.5px solid var(--border-color); transition: all 0.2s;">
                            <input type="checkbox" id="location_required" name="location_required" value="1" style="width: 18px; height: 18px; accent-color: var(--accent);">
                            <span id="loc-toggle-text">Nonaktif</span>
                        </label>
                    </div>

                    <div id="location-config" style="display: none;">
                        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 14px;">
                            <div class="form-group">
                                <label for="latitude" class="form-label">Latitude <span style="color: var(--danger);">*</span></label>
                                <input type="number" id="latitude" name="latitude" class="form-control" step="any" placeholder="-6.9824" value="{{ old('latitude') }}">
                            </div>
                            <div class="form-group">
                                <label for="longitude" class="form-label">Longitude <span style="color: var(--danger);">*</span></label>
                                <input type="number" id="longitude" name="longitude" class="form-control" step="any" placeholder="110.4121" value="{{ old('longitude') }}">
                            </div>
                        </div>

                        <div class="form-group" style="max-width: 300px; margin-bottom: 14px;">
                            <label for="location_radius" class="form-label">Radius Toleransi (meter)</label>
                            <input type="number" id="location_radius" name="location_radius" class="form-control" min="10" max="2000" placeholder="100" value="{{ old('location_radius', 100) }}">
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Rekomendasi: 50–200 meter untuk dalam gedung.</small>
                        </div>

                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                            <i class="fa-solid fa-circle-info" style="color: var(--accent);"></i>
                            Klik marker <strong>📍</strong> di peta untuk memilih koordinat, atau ketik manual di atas.
                        </p>
                        <div id="location-map"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary">Simpan Mata Kuliah</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">Batalkan</a>
                </div>

            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    const toggle     = document.getElementById('location_required');
    const config     = document.getElementById('location-config');
    const toggleText = document.getElementById('loc-toggle-text');
    const toggleLabel= document.getElementById('loc-toggle-label');
    const latInput   = document.getElementById('latitude');
    const lngInput   = document.getElementById('longitude');

    // Koordinat default: UDINUS Semarang
    const DEFAULT_LAT = -6.9835;
    const DEFAULT_LNG = 110.4112;

    let map, marker;

    function initMap() {
        if (map) return;
        const lat = parseFloat(latInput.value) || DEFAULT_LAT;
        const lng = parseFloat(lngInput.value) || DEFAULT_LNG;

        map = L.map('location-map').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([lat, lng], { draggable: true }).addTo(map)
            .bindPopup('📍 Lokasi Kelas').openPopup();

        // Update input saat marker di-drag
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            latInput.value = pos.lat.toFixed(8);
            lngInput.value = pos.lng.toFixed(8);
        });

        // Klik peta untuk pindah marker
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(8);
            lngInput.value = e.latlng.lng.toFixed(8);
        });
    }

    // Sinkronisasi input manual → marker
    [latInput, lngInput].forEach(input => {
        input.addEventListener('input', function() {
            if (!map) return;
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng]);
            }
        });
    });

    function updateToggle() {
        if (toggle.checked) {
            config.style.display = 'block';
            toggleText.textContent = 'Aktif';
            toggleLabel.style.borderColor = 'var(--accent)';
            toggleLabel.style.background = 'rgba(14,165,233,0.07)';
            setTimeout(() => { initMap(); map && map.invalidateSize(); }, 100);
        } else {
            config.style.display = 'none';
            toggleText.textContent = 'Nonaktif';
            toggleLabel.style.borderColor = 'var(--border-color)';
            toggleLabel.style.background = '';
        }
    }

    toggle.addEventListener('change', updateToggle);
    updateToggle();
})();
</script>
@endpush
