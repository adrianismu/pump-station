<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationContent;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EducationController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'video_url' => 'nullable|string|url',
            'infographic' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            'published' => 'nullable|boolean',
        ]);
        
        // Ensure published is boolean
        $validated['published'] = $request->boolean('published', true);
        
        // Upload main image
        $imageResult = $this->imageUploadService->uploadImage(
            $request->file('image'),
            'education/thumbnails'
        );
        
        $validated['image'] = $imageResult['url'];
        $validated['cloudinary_id'] = $imageResult['cloudinary_id'];
        
        // Upload infographic if provided
        if ($request->hasFile('infographic')) {
            $infographicResult = $this->imageUploadService->uploadImage(
                $request->file('infographic'),
                'education/infographics'
            );
            
            $validated['infographic_url'] = $infographicResult['url'];
            $validated['infographic_cloudinary_id'] = $infographicResult['cloudinary_id'];
        }
        
        $validated['date'] = now();
        
        $educationContent = EducationContent::create($validated);
        
        return redirect()->route('admin.education')->with('success', 'Konten edukasi berhasil ditambahkan.');
    }
    
    public function show(EducationContent $educationContent)
    {
        // Get related content based on type and recent content
            $relatedContents = EducationContent::where('id', '!=', $educationContent->id)
            ->where('type', $educationContent->type)
                ->orderBy('date', 'desc')
                ->limit(4)
                ->get();
        
        // If we don't have enough related content by type, add some recent content
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'video_url' => 'nullable|string|url',
            'infographic' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            'published' => 'nullable|boolean',
        ]);
        
        // Ensure published is boolean
        $validated['published'] = $request->boolean('published', $educationContent->published);
        
        // Handle main image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($educationContent->image) {
                $this->imageUploadService->deleteImage(
                    $educationContent->image,
                    $educationContent->cloudinary_id
                );
            }
            
            // Upload new image
            $imageResult = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'education/thumbnails'
            );
            
            $validated['image'] = $imageResult['url'];
            $validated['cloudinary_id'] = $imageResult['cloudinary_id'];
        }
        
        // Handle infographic update
        if ($request->hasFile('infographic')) {
            // Delete old infographic if exists
            if ($educationContent->infographic_url) {
                $this->imageUploadService->deleteImage(
                    $educationContent->infographic_url,
                    $educationContent->infographic_cloudinary_id
                );
            }
            
            // Upload new infographic
            $infographicResult = $this->imageUploadService->uploadImage(
                $request->file('infographic'),
                'education/infographics'
            );
            
            $validated['infographic_url'] = $infographicResult['url'];
            $validated['infographic_cloudinary_id'] = $infographicResult['cloudinary_id'];
        }
        
        $validated['updated_at'] = now();
        
        $educationContent->update($validated);
        
        return redirect()->route('admin.education.show', $educationContent->id)->with('success', 'Konten edukasi berhasil diperbarui.');
    }
    
    public function destroy(EducationContent $educationContent)
    {
        // Delete associated images
        if ($educationContent->image) {
            $this->imageUploadService->deleteImage(
                $educationContent->image,
                $educationContent->cloudinary_id
            );
        }
        
        if ($educationContent->infographic_url) {
            $this->imageUploadService->deleteImage(
                $educationContent->infographic_url,
                $educationContent->infographic_cloudinary_id
            );
        }
        
        $educationContent->delete();
        
        return redirect()->route('admin.education')->with('success', 'Konten edukasi berhasil dihapus.');
    }
}