<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    /**
     * Upload image to cloud storage (Cloudinary) or local storage as fallback
     * Returns array with url and cloudinary_id if applicable
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads'): array
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
                
                return [
                    'url' => $result->getSecurePath(),
                    'cloudinary_id' => $result->getPublicId(),
                    'is_cloudinary' => true
                ];
            }
        } catch (\Exception $e) {
            // Log error but continue with local storage
            Log::warning('Cloudinary upload failed: ' . $e->getMessage());
        }
        
        // Fallback to local storage
        $path = $file->store($folder, 'public');
        return [
            'url' => asset('storage/' . $path),
            'cloudinary_id' => null,
            'is_cloudinary' => false
        ];
    }
    
    /**
     * Upload multiple images
     * Returns array of upload results
     */
    public function uploadMultipleImages(array $files, string $folder = 'uploads'): array
    {
        $uploadedResults = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedResults[] = $this->uploadImage($file, $folder);
            }
        }
        
        return $uploadedResults;
    }
    
    /**
     * Upload image from URL (for migrating existing URLs to Cloudinary)
     */
    public function uploadFromUrl(string $imageUrl, string $folder = 'uploads'): array
    {
        try {
            if ($this->isCloudinaryConfigured()) {
                $result = Cloudinary::upload($imageUrl, [
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                return [
                    'url' => $result->getSecurePath(),
                    'cloudinary_id' => $result->getPublicId(),
                    'is_cloudinary' => true
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Cloudinary upload from URL failed: ' . $e->getMessage());
        }
        
        // Return original URL if Cloudinary upload fails
        return [
            'url' => $imageUrl,
            'cloudinary_id' => null,
            'is_cloudinary' => false
        ];
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
    public function deleteImage(string $imageUrl, ?string $cloudinaryId = null): bool
    {
        try {
            // If we have cloudinary_id, use it to delete from Cloudinary
            if ($cloudinaryId) {
                Cloudinary::destroy($cloudinaryId);
                return true;
            }
            
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
            Log::warning('Image deletion failed: ' . $e->getMessage());
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
    
    /**
     * Get optimized image URL with transformations
     */
    public function getOptimizedUrl(string $cloudinaryId, array $transformations = []): string
    {
        if (!$this->isCloudinaryConfigured() || !$cloudinaryId) {
            return '';
        }
        
        try {
            $defaultTransformations = [
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ];
            
            $transformations = array_merge($defaultTransformations, $transformations);
            
            return Cloudinary::getUrl($cloudinaryId, $transformations);
        } catch (\Exception $e) {
            Log::warning('Failed to generate optimized URL: ' . $e->getMessage());
            return '';
        }
    }
} 