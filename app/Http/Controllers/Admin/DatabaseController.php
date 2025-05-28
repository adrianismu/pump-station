<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DatabaseController extends Controller
{
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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
            'capacity' => 'required|string',
            'pump_count' => 'required|integer|min:1',
            'water_level' => 'nullable|string',
            'image' => 'required|string',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'staff_count' => 'required|integer|min:1',
        ]);
        
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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'required|string|in:Aktif,Perlu Perhatian,Tidak Aktif',
            'capacity' => 'required|string',
            'pump_count' => 'required|integer|min:1',
            'water_level' => 'nullable|string',
            'image' => 'required|string',
            'built_year' => 'required|integer|min:1900|max:2100',
            'manager_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'staff_count' => 'required|integer|min:1',
        ]);
        
        $validated['last_updated'] = now();
        
        $pumpHouse->update($validated);
        
        return redirect()->route('admin.database')->with('success', 'Rumah pompa berhasil diperbarui.');
    }
    
    public function destroy(PumpHouse $pumpHouse)
    {
        $pumpHouse->delete();
        
        return redirect()->route('admin.database')->with('success', 'Rumah pompa berhasil dihapus.');
    }
}