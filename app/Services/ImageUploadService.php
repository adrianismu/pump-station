<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageUploadService
{
    /**
     * Upload image to cloud storage (Cloudinary) or local storage as fallback
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads'): string
    {
        try {
            // Try to upload to Cloudinary if configured
            if ($this->isCloudinaryConfigured()) {
                $result = Cloudinary::upload($file->getRealPath(), [
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                return $result->getSecurePath();
            }
        } catch (\Exception $e) {
            // Log error but continue with local storage
            \Log::warning('Cloudinary upload failed: ' . $e->getMessage());
        }
        
        // Fallback to local storage
        $path = $file->store($folder, 'public');
        return asset('storage/' . $path);
    }
    
    /**
     * Upload multiple images
     */
    public function uploadMultipleImages(array $files, string $folder = 'uploads'): array
    {
        $uploadedPaths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedPaths[] = $this->uploadImage($file, $folder);
            }
        }
        
        return $uploadedPaths;
    }
    
    /**
     * Check if Cloudinary is properly configured
     */
    private function isCloudinaryConfigured(): bool
    {
        return !empty(config('cloudinary.cloud_url')) || 
               (!empty(env('CLOUDINARY_CLOUD_NAME')) && 
                !empty(env('CLOUDINARY_API_KEY')) && 
                !empty(env('CLOUDINARY_API_SECRET')));
    }
    
    /**
     * Delete image from storage
     */
    public function deleteImage(string $imageUrl): bool
    {
        try {
            // If it's a Cloudinary URL, extract public_id and delete from Cloudinary
            if (str_contains($imageUrl, 'cloudinary.com')) {
                $publicId = $this->extractCloudinaryPublicId($imageUrl);
                if ($publicId) {
                    Cloudinary::destroy($publicId);
                    return true;
                }
            }
            
            // If it's a local storage URL, delete from local storage
            if (str_contains($imageUrl, '/storage/')) {
                $path = str_replace(asset('storage/'), '', $imageUrl);
                return Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            \Log::warning('Image deletion failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Extract public_id from Cloudinary URL
     */
    private function extractCloudinaryPublicId(string $url): ?string
    {
        // Extract public_id from Cloudinary URL
        // Example: https://res.cloudinary.com/demo/image/upload/v1234567890/folder/image.jpg
        $pattern = '/\/v\d+\/(.+)\.[a-zA-Z]+$/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
} 