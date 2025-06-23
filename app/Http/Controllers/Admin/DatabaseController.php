<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DatabaseController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function index()
    {
        $pumpHouses = PumpHouse::all();
        
        return Inertia::render('Admin/Database/Index', [
            'pumpHouses' => $pumpHouses,
        ]);
    }

    public function show($id)
    {
        $user = auth()->user();
        
        // Authorization check untuk detail page
        if (!$user->isAdmin()) {
            // Petugas hanya bisa akses pump house yang ditugaskan
            if (!$user->hasAccessToPumpHouse($id, 'read')) {
                abort(403, 'Anda tidak memiliki akses ke rumah pompa ini.');
            }
        }

        $pumpHouse = PumpHouse::with([
            'waterLevelHistory' => function($query) {
                $query->orderBy('recorded_at', 'desc')->limit(100);
            },
            'threshold_settings' => function($query) {
                $query->where('is_active', true)->orderBy('water_level', 'asc');
            }
        ])->findOrFail($id);

        // Tentukan apakah user bisa edit
        $canEdit = $user->isAdmin() || $user->hasAccessToPumpHouse($id, 'write');

        return Inertia::render('Admin/Database/Show', [
            'pumpHouse' => $pumpHouse,
            'waterLevelHistory' => $pumpHouse->waterLevelHistory,
            'activeThresholds' => $pumpHouse->threshold_settings,
            'canEdit' => $canEdit,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Authorization check untuk create
        if (!$user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat rumah pompa baru.');
        }
        
        return Inertia::render('Admin/Database/Create', [
            'title' => 'Tambah Rumah Pompa',
            'breadcrumbs' => [
                ['label' => 'Database', 'url' => route('admin.database')],
                ['label' => 'Tambah Baru', 'url' => '#'],
            ],
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
            'capacity' => 'required|string',
            'pump_count' => 'required|integer|min:1',
            'active_pumps' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'staff_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);
        
        // Validate that active_pumps doesn't exceed pump_count
        if ($validated['active_pumps'] > $validated['pump_count']) {
            return back()->withErrors([
                'active_pumps' => 'Jumlah pompa aktif tidak boleh lebih besar dari total pompa'
            ])->withInput();
        }
        
        // Upload image using ImageUploadService
        $imageResult = $this->imageUploadService->uploadImage(
            $request->file('image'),
            'pump-houses'
        );
        
        $validated['image'] = $imageResult['url'];
        $validated['cloudinary_id'] = $imageResult['cloudinary_id'];
        $validated['last_updated'] = now();
        
        $pumpHouse = PumpHouse::create($validated);
        
        return redirect()->route('admin.database')->with('success', 'Rumah pompa berhasil ditambahkan.');
    }
    
    public function edit(PumpHouse $pumpHouse)
    {
        $user = auth()->user();
        
        // Authorization check untuk edit
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouse->id, 'write')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit rumah pompa ini.');
        }

        return Inertia::render('Admin/Database/Edit', [
            'pumpHouse' => $pumpHouse,
        ]);
    }
    
    public function update(Request $request, PumpHouse $pumpHouse)
    {
        $user = auth()->user();
        
        // Authorization check untuk update
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouse->id, 'write')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit rumah pompa ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
            'capacity' => 'required|string',
            'pump_count' => 'required|integer|min:1',
            'active_pumps' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'staff_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);
        
        // Validate that active_pumps doesn't exceed pump_count
        if ($validated['active_pumps'] > $validated['pump_count']) {
            return back()->withErrors([
                'active_pumps' => 'Jumlah pompa aktif tidak boleh lebih besar dari total pompa'
            ])->withInput();
        }
        
        // Handle image update if new image is uploaded
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($pumpHouse->image) {
                $this->imageUploadService->deleteImage(
                    $pumpHouse->image,
                    $pumpHouse->cloudinary_id
                );
            }
            
            // Upload new image
            $imageResult = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'pump-houses'
            );
            
            $validated['image'] = $imageResult['url'];
            $validated['cloudinary_id'] = $imageResult['cloudinary_id'];
        }
        
        $validated['last_updated'] = now();
        
        $pumpHouse->update($validated);
        
        return redirect()->route('admin.database.show', $pumpHouse->id)
            ->with('success', 'Rumah pompa berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, PumpHouse $pumpHouse)
    {
        $user = auth()->user();
        
        // Authorization check untuk toggle status
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouse->id, 'write')) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah status rumah pompa ini.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
        ]);

        $pumpHouse->update([
            'status' => $validated['status'],
            'last_updated' => now(),
        ]);

        $statusText = $validated['status'] === 'Aktif' ? 'diaktifkan' : ($validated['status'] === 'Perlu Perhatian' ? 'diperlukan perhatian' : 'dinonaktifkan');
        
        return redirect()->route('admin.database.show', $pumpHouse->id)
            ->with('success', "Rumah pompa berhasil {$statusText}.");
    }
    
    public function destroy(PumpHouse $pumpHouse)
    {
        // Delete associated image
        if ($pumpHouse->image) {
            $this->imageUploadService->deleteImage(
                $pumpHouse->image,
                $pumpHouse->cloudinary_id
            );
        }
        
        $pumpHouse->delete();
        
        return redirect()->route('admin.database')->with('success', 'Rumah pompa berhasil dihapus.');
    }
}