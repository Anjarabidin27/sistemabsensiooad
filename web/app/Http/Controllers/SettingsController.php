<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\SettingsService;
use App\Services\AiEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private SettingsService $settings;
    private AiEngineService $aiEngine;

    public function __construct(SettingsService $settings, AiEngineService $aiEngine)
    {
        $this->settings = $settings;
        $this->aiEngine = $aiEngine;
    }

    /** Show system settings page. */
    public function index()
    {
        // Get all system settings grouped by their group
        $settingsGrouped = SystemSetting::orderBy('group')->get()->groupBy('group');
        
        // AI engine health status
        $aiHealth = $this->aiEngine->health();

        return view('admin.settings', compact('settingsGrouped', 'aiHealth'));
    }

    /** Update system settings. */
    public function update(Request $request)
    {
        $settings = SystemSetting::all();
        
        $rules = [];
        foreach ($settings as $setting) {
            if ($setting->type === 'file') {
                $rules[$setting->key] = 'nullable|image|max:2048';
            } elseif ($setting->type === 'boolean') {
                $rules[$setting->key] = 'nullable|string'; // will be converted
            } elseif ($setting->type === 'integer') {
                $rules[$setting->key] = 'required|integer';
            } else {
                $rules[$setting->key] = 'required|string|max:255';
            }
        }

        $validated = $request->validate($rules);

        foreach ($settings as $setting) {
            $key = $setting->key;

            if ($setting->type === 'file') {
                if ($request->hasFile($key)) {
                    // Delete old logo file if exists
                    if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }
                    $path = $request->file($key)->store('logos', 'public');
                    $this->settings->set($key, $path);
                }
            } elseif ($setting->type === 'boolean') {
                // If not checked in checkbox, value is '0', else '1'
                $value = $request->has($key) ? '1' : '0';
                $this->settings->set($key, $value);
            } else {
                if (isset($validated[$key])) {
                    $this->settings->set($key, $validated[$key]);
                }
            }
        }

        return redirect()->route('admin.settings')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
