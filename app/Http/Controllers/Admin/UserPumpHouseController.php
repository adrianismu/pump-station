<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PumpHouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UserPumpHouseController extends Controller
{
    public function index()
    {
        $users = User::with(['allPumpHouses' => function($query) {
            $query->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes']);
        }])->where('role', 'petugas')->get();

        $pumpHouses = PumpHouse::all();

        return Inertia::render('Admin/UserPumpHouse/Index', [
            'users' => $users,
            'pumpHouses' => $pumpHouses,
        ]);
    }

    public function show($userId)
    {
        $user = User::with(['allPumpHouses' => function($query) {
            $query->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes']);
        }])->findOrFail($userId);

        $availablePumpHouses = PumpHouse::whereNotIn('id', 
            $user->allPumpHouses->pluck('id')
        )->get();

        return Inertia::render('Admin/UserPumpHouse/Show', [
            'user' => $user,
            'availablePumpHouses' => $availablePumpHouses,
        ]);
    }

    public function assignPumpHouse(Request $request, $userId)
    {
        $request->validate([
            'pump_house_id' => 'required|exists:pump_houses,id',
            'access_level' => 'required|in:read,write',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($userId);

        // Cek apakah sudah ada assignment
        $existing = $user->allPumpHouses()->where('pump_house_id', $request->pump_house_id)->first();
        
        if ($existing) {
            return back()->withErrors(['pump_house_id' => 'User sudah memiliki akses ke rumah pompa ini.']);
        }

        $user->pumpHouses()->attach($request->pump_house_id, [
            'access_level' => $request->access_level,
            'is_active' => true,
            'assigned_at' => now(),
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Akses rumah pompa berhasil diberikan.');
    }

    public function updateAccess(Request $request, $userId, $pumpHouseId)
    {
        $request->validate([
            'access_level' => 'required|in:read,write',
            'is_active' => 'required|boolean',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($userId);

        $user->allPumpHouses()->updateExistingPivot($pumpHouseId, [
            'access_level' => $request->access_level,
            'is_active' => $request->is_active,
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Akses rumah pompa berhasil diperbarui.');
    }

    public function revokeAccess($userId, $pumpHouseId)
    {
        $user = User::findOrFail($userId);
        $user->allPumpHouses()->detach($pumpHouseId);

        return back()->with('success', 'Akses rumah pompa berhasil dicabut.');
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'pump_house_ids' => 'required|array',
            'pump_house_ids.*' => 'exists:pump_houses,id',
            'access_level' => 'required|in:read,write',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            
            foreach ($request->pump_house_ids as $pumpHouseId) {
                // Cek apakah sudah ada assignment
                $existing = $user->allPumpHouses()->where('pump_house_id', $pumpHouseId)->first();
                
                if (!$existing) {
                    $user->pumpHouses()->attach($pumpHouseId, [
                        'access_level' => $request->access_level,
                        'is_active' => true,
                        'assigned_at' => now(),
                        'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
                        'notes' => $request->notes,
                    ]);
                    $assignedCount++;
                } else {
                    $skippedCount++;
                }
            }
        }

        $message = "Berhasil memberikan {$assignedCount} akses.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} akses dilewati karena sudah ada.";
        }

        return back()->with('success', $message);
    }

    public function copyAccess(Request $request)
    {
        $request->validate([
            'source_user_id' => 'required|exists:users,id',
            'target_user_ids' => 'required|array',
            'target_user_ids.*' => 'exists:users,id',
        ]);

        $sourceUser = User::with('allPumpHouses')->find($request->source_user_id);
        $copiedCount = 0;

        foreach ($request->target_user_ids as $targetUserId) {
            if ($targetUserId == $request->source_user_id) continue;

            $targetUser = User::find($targetUserId);
            
            foreach ($sourceUser->allPumpHouses as $pumpHouse) {
                // Cek apakah target user sudah memiliki akses
                $existing = $targetUser->allPumpHouses()->where('pump_house_id', $pumpHouse->id)->first();
                
                if (!$existing) {
                    $targetUser->pumpHouses()->attach($pumpHouse->id, [
                        'access_level' => $pumpHouse->pivot->access_level,
                        'is_active' => true,
                        'assigned_at' => now(),
                        'expires_at' => $pumpHouse->pivot->expires_at,
                        'notes' => 'Disalin dari ' . $sourceUser->name,
                    ]);
                    $copiedCount++;
                }
            }
        }

        return back()->with('success', "Berhasil menyalin {$copiedCount} akses.");
    }
} 
 