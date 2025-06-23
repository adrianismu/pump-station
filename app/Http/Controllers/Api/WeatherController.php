<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    protected WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get weather forecast for a specific location
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $weatherData = $this->weatherService->getWeatherForecast(
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$weatherData) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cuaca',
                'data' => null
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data cuaca berhasil diambil',
            'data' => $weatherData,
            'meta' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'cached' => true, // Since we're using cache, this is likely cached
                'updated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get weather forecast for a pump house
     * 
     * @param Request $request
     * @param int $pumpHouseId
     * @return JsonResponse
     */
    public function pumpHouse(Request $request, int $pumpHouseId): JsonResponse
    {
        $pumpHouse = \App\Models\PumpHouse::find($pumpHouseId);

        if (!$pumpHouse) {
            return response()->json([
                'success' => false,
                'message' => 'Rumah pompa tidak ditemukan',
                'data' => null
            ], 404);
        }

        // Check if user has access to this pump house
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'read')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak',
                'data' => null
            ], 403);
        }

        $weatherData = $this->weatherService->getWeatherForecast(
            $pumpHouse->lat,
            $pumpHouse->lng
        );

        if (!$weatherData) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cuaca untuk rumah pompa',
                'data' => null
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data cuaca rumah pompa berhasil diambil',
            'data' => $weatherData,
            'meta' => [
                'pump_house_id' => $pumpHouseId,
                'pump_house_name' => $pumpHouse->name,
                'latitude' => $pumpHouse->lat,
                'longitude' => $pumpHouse->lng,
                'cached' => true,
                'updated_at' => now()->toISOString()
            ]
        ]);
    }
} 