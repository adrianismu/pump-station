<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function getWaterLevelAlerts()
    {
        $alerts = Alert::with('pumpHouse')
            ->where('is_active', true)
            ->whereNotNull('water_level')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }
    
    public function markAllAsRead()
    {
        Alert::where('is_active', true)
            ->whereNotNull('water_level')
            ->update(['is_active' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'All water level alerts marked as read'
        ]);
    }
}