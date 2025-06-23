<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThresholdSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThresholdSettingController extends Controller
{
    public function index()
    {
        $thresholds = ThresholdSetting::orderBy('water_level', 'asc')->get();

        return Inertia::render('Admin/ThresholdSettings/Index', [
            'thresholds' => $thresholds,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ThresholdSettings/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:threshold_settings',
            'label' => 'required|string|max:255',
            'water_level' => 'required|numeric|min:0|max:10',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'severity' => 'required|in:low,medium,high,critical',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        ThresholdSetting::create($request->all());

        return redirect()->route('admin.threshold-settings.index')
            ->with('success', 'Pengaturan threshold berhasil ditambahkan');
    }

    public function show(ThresholdSetting $thresholdSetting)
    {
        return Inertia::render('Admin/ThresholdSettings/Show', [
            'threshold' => $thresholdSetting,
        ]);
    }

    public function edit(ThresholdSetting $thresholdSetting)
    {
        return Inertia::render('Admin/ThresholdSettings/Edit', [
            'threshold' => $thresholdSetting,
        ]);
    }

    public function update(Request $request, ThresholdSetting $thresholdSetting)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:threshold_settings,name,' . $thresholdSetting->id,
            'label' => 'required|string|max:255',
            'water_level' => 'required|numeric|min:0|max:10',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'severity' => 'required|in:low,medium,high,critical',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $thresholdSetting->update($request->all());

        return redirect()->route('admin.threshold-settings.index')
            ->with('success', 'Pengaturan threshold berhasil diperbarui');
    }

    public function destroy(ThresholdSetting $thresholdSetting)
    {
        $thresholdSetting->delete();

        return redirect()->route('admin.threshold-settings.index')
            ->with('success', 'Pengaturan threshold berhasil dihapus');
    }
}
