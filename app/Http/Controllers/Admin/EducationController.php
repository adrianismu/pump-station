<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EducationController extends Controller
{
    public function index()
    {
        $educationContents = EducationContent::orderBy('date', 'desc')->get();
        
        return Inertia::render('Admin/Education/Index', [
            'educationContents' => $educationContents,
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Admin/Education/Create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|in:Artikel,Video,Infografis',
            'image' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'nullable|string|url',
            'infographic_url' => 'nullable|string|url',
            'tags' => 'nullable|array',
            'published' => 'boolean',
        ]);
        
        $validated['date'] = now();
        $validated['views'] = 0;
        
        $educationContent = EducationContent::create($validated);
        
        return redirect()->route('admin.education')->with('success', 'Konten edukasi berhasil ditambahkan.');
    }
    
    public function show(EducationContent $educationContent)
    {
        // Get related content based on tags
        $relatedContents = collect([]);
        
        if (!empty($educationContent->tags)) {
            $relatedContents = EducationContent::where('id', '!=', $educationContent->id)
                ->where(function ($query) use ($educationContent) {
                    foreach ($educationContent->tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                })
                ->orderBy('date', 'desc')
                ->limit(4)
                ->get();
        }
        
        // If we don't have enough related content by tags, add some recent content
        if ($relatedContents->count() < 4) {
            $additionalContent = EducationContent::where('id', '!=', $educationContent->id)
                ->whereNotIn('id', $relatedContents->pluck('id')->toArray())
                ->orderBy('date', 'desc')
                ->limit(4 - $relatedContents->count())
                ->get();
                
            $relatedContents = $relatedContents->concat($additionalContent);
        }
        
        return Inertia::render('Admin/Education/Show', [
            'educationContent' => $educationContent,
            'relatedContents' => $relatedContents,
        ]);
    }
    
    public function edit(EducationContent $educationContent)
    {
        return Inertia::render('Admin/Education/Edit', [
            'educationContent' => $educationContent,
        ]);
    }
    
    public function update(Request $request, EducationContent $educationContent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|in:Artikel,Video,Infografis',
            'image' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'nullable|string|url',
            'infographic_url' => 'nullable|string|url',
            'tags' => 'nullable|array',
            'published' => 'boolean',
            'views' => 'required|integer|min:0',
        ]);
        
        $validated['updated_at'] = now();
        
        $educationContent->update($validated);
        
        return redirect()->route('admin.education.show', $educationContent->id)->with('success', 'Konten edukasi berhasil diperbarui.');
    }
    
    public function destroy(EducationContent $educationContent)
    {
        $educationContent->delete();
        
        return redirect()->route('admin.education')->with('success', 'Konten edukasi berhasil dihapus.');
    }
}