<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use Illuminate\Http\Request;

class PumpHouseController extends Controller
{
    public function index()
    {
        $pumpHouses = PumpHouse::all();
        return response()->json($pumpHouses);
    }

    public function show($id)
    {
        $pumpHouse = PumpHouse::findOrFail($id);
        return response()->json($pumpHouse);
    }

    public function store(Request $request)
    {
        // Implementation for creating pump house
        return response()->json(['message' => 'Not implemented yet'], 501);
    }

    public function update(Request $request, $id)
    {
        // Implementation for updating pump house
        return response()->json(['message' => 'Not implemented yet'], 501);
    }

    public function destroy($id)
    {
        // Implementation for deleting pump house
        return response()->json(['message' => 'Not implemented yet'], 501);
    }
}
