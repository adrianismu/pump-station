<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filter reports berdasarkan akses user
        $reportsQuery = Report::with('pump_house');
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $reportsQuery->whereIn('pump_house_id', $accessibleIds);
        }
        
        $reports = $reportsQuery->orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'userRole' => $user->role,
        ]);
    }
    
    public function show(Report $report)
    {
        $user = auth()->user();
        
        // Cek akses untuk petugas
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($report->pump_house_id)) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
        
        // Load the report with its relationships
        $report->load([
            'responses' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'pump_house',
        ]);
        
        // Get related reports (e.g., reports in the same area or with similar issues)
        $relatedReportsQuery = Report::where('id', '!=', $report->id)
            ->where(function ($query) use ($report) {
                // Reports in the same location
                $query->where('location', 'like', '%' . $report->location . '%')
                    // Or reports with similar titles
                    ->orWhere('title', 'like', '%' . explode(' ', $report->title)[0] . '%');
            });
            
        // Filter related reports untuk petugas
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $relatedReportsQuery->whereIn('pump_house_id', $accessibleIds);
        }
        
        $relatedReports = $relatedReportsQuery->limit(3)->get();
        
        return Inertia::render('Admin/Reports/Show', [
            'report' => $report,
            'relatedReports' => $relatedReports,
            'userRole' => $user->role,
        ]);
    }
    
    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Belum Ditanggapi,Sedang Diproses,Selesai',
            'note' => 'nullable|string',
        ]);
        
        $report->update([
            'status' => $validated['status']
        ]);
        
        // Add a response if a note was provided
        if (!empty($validated['note'])) {
            ReportResponse::create([
                'report_id' => $report->id,
                'user_id' => auth()->id(),
                'content' => $validated['note'],
                'status_change' => $validated['status'],
            ]);
        }
        
        return redirect()->back();
    }
    
    public function addResponse(Request $request, Report $report)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'status' => 'nullable|string|in:Belum Ditanggapi,Sedang Diproses,Selesai',
        ]);
        
        // Create the response
        ReportResponse::create([
            'report_id' => $report->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'status_change' => $validated['status'] ?? null,
        ]);
        
        // Update report status if provided
        if (!empty($validated['status'])) {
            $report->update([
                'status' => $validated['status']
            ]);
        }
        
        return redirect()->back();
    }
    
    public function destroy(Report $report)
    {
        // Delete the report
        $report->delete();
        
        return redirect()->route('admin.reports');
    }
}