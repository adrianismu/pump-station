<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\PumpHouse;
use App\Models\Report;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = [
            'totalPumpHouses' => PumpHouse::count(),
            'activePumpHouses' => PumpHouse::where('status', 'Aktif')->count(),
            'totalPumps' => PumpHouse::sum('pump_count'),
            'activePumps' => PumpHouse::where('status', 'Aktif')->sum('pump_count'),
            'recentAlerts' => Alert::with('pump_house')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'recentReports' => Report::orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];
        
        return response()->json($dashboardData);
    }
}
