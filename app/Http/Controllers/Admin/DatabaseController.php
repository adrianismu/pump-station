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
    
    public function create()
    {
        return Inertia::render('Admin/Database/Create');
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'staff_count' => 'required|integer|min:1',
        ]);
        
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
        return Inertia::render('Admin/Database/Edit', [
            'pumpHouse' => $pumpHouse,
        ]);
    }
    
    public function update(Request $request, PumpHouse $pumpHouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
            'capacity' => 'required|string',
            'pump_count' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'staff_count' => 'required|integer|min:1',
        ]);
        
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
        
        return redirect()->route('admin.database')->with('success', 'Rumah pompa berhasil diperbarui.');
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