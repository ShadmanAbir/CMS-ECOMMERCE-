<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ImageUploadService
{
    protected $disk = 'uploads';
    
    /**
     * Upload an image and return the path
     *
     * @param UploadedFile $file
     * @param string $folder (banners, categories, products, etc.)
     * @return string Path to saved image
     */
    public function upload(UploadedFile $file, string $folder = 'general'): string
    {
        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Define storage path
        $path = "{$folder}/{$filename}";
        
        // Store file using Laravel Storage facade
        $content = file_get_contents($file->getRealPath());
        Storage::disk($this->disk)->put($path, $content);
        
        // Return the relative path for database storage
        \Log::info("Image uploaded successfully to storage: {$path}");
        
        return $path;
    }
    
    /**
     * Upload multiple images
     *
     * @param array $files
     * @param string $folder
     * @return array Array of paths
     */
    public function uploadMultiple(array $files, string $folder = 'general'): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $folder);
            }
        }
        
        return $paths;
    }
    
    /**
     * Delete an image file
     *
     * @param string $path Relative path in storage
     * @return bool
     */
    public function delete(string $path): bool
    {
        // Normalize: strip 'uploads/' prefix if present since disk root already includes it
        if (strpos($path, 'uploads/') === 0) {
            $path = substr($path, 8);
        }

        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }
        
        return false;
    }
    
    /**
     * Update an image (delete old, upload new)
     *
     * @param UploadedFile $newFile
     * @param string|null $oldPath
     * @param string $folder
     * @return string New path
     */
    public function update(UploadedFile $newFile, ?string $oldPath, string $folder = 'general'): string
    {
        // Delete old image if exists
        if ($oldPath) {
            $this->delete($oldPath);
        }
        
        // Upload new image
        return $this->upload($newFile, $folder);
    }
    
    /**
     * Get the full URL for an image
     *
     * @param string $path Relative path in storage
     * @return string Full URL
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
