@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')
@section('header-title', 'Pengaturan Sistem')
@section('header-subtitle', 'Sesuaikan identitas universitas, threshold pencocokan wajah, dan tema tampilan')

@section('content')

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
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

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            @foreach($settingsGrouped as $groupName => $settings)
                <div class="card">
                    <div class="card-header" style="background-color: rgba(27, 42, 107, 0.02);">
                        <span class="card-title" style="text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">
                            @if($groupName === 'identity')
                                <i class="fa-solid fa-graduation-cap" style="margin-right: 6px;"></i> Identitas Universitas / Kampus
                            @elseif($groupName === 'theme')
                                <i class="fa-solid fa-palette" style="margin-right: 6px;"></i> Tampilan & Tema Visual
                            @elseif($groupName === 'attendance')
                                <i class="fa-solid fa-circle-user" style="margin-right: 6px;"></i> Konfigurasi AI & Kehadiran
                            @elseif($groupName === 'notification')
                                <i class="fa-solid fa-bell" style="margin-right: 6px;"></i> Pengaturan Notifikasi & Waktu
                            @else
                                <i class="fa-solid fa-sliders" style="margin-right: 6px;"></i> Grup: {{ $groupName }}
                            @endif
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            @foreach($settings as $setting)
                                <div class="form-group" style="margin-bottom: 0; display: grid; grid-template-columns: 240px 1fr; gap: 20px; align-items: center;">
                                    
                                    <label for="{{ $setting->key }}" class="form-label" style="margin-bottom: 0;">
                                        {{ $setting->label }}
                                        <span style="display: block; font-size: 0.725rem; font-weight: 400; color: var(--text-muted); font-family: monospace; margin-top: 2px;">
                                            {{ $setting->key }}
                                        </span>
                                    </label>
                                    
                                    <div>
                                        @if($setting->type === 'boolean')
                                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                                                <input type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="1" style="width: 20px; height: 20px; accent-color: var(--primary);" {{ $setting->value == '1' ? 'checked' : '' }}>
                                                <span>Aktif</span>
                                            </label>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ $setting->value }}" style="max-width: 150px;">
                                        @elseif($setting->type === 'file')
                                            <div style="display: flex; align-items: center; gap: 16px;">
                                                <input type="file" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" accept="image/*" style="padding: 6px 12px; font-size: 0.8rem; max-width: 300px;">
                                                @if($setting->value)
                                                    <img src="{{ asset('storage/' . $setting->value) }}" alt="Logo Preview" style="height: 36px; object-fit: contain; border: 1px solid var(--border-color); padding: 2px; border-radius: var(--radius-sm);">
                                                @endif
                                            </div>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ $setting->value }}">
                                        @endif
                                    </div>
                                    
                                </div>
                                @if(!$loop->last)
                                    <div style="border-bottom: 1px solid var(--border-color); opacity: 0.5;"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Action buttons -->
            <div class="card" style="position: sticky; bottom: 20px; z-index: 10; box-shadow: var(--shadow-lg); border-color: rgba(27,42,107,0.15);">
                <div class="card-body" style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">
                        Pastikan semua parameter konfigurasi sesuai sebelum disimpan.
                    </span>
                    <button type="submit" class="btn btn-primary" style="min-width: 180px;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Konfigurasi</span>
                    </button>
                </div>
            </div>

        </div>

    </form>

@endsection
