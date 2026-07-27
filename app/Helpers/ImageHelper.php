<?php

if (!function_exists('storage_asset')) {
    /**
     * Get the URL for a file stored in the uploads disk
     *
     * @param string $path
     * @return string
     */
    function storage_asset($path)
    {
        // If path starts with 'uploads/', it's from the old system
        if (strpos($path, 'uploads/') === 0) {
            $path = substr($path, 8); // Remove 'uploads/' prefix
        }
        
        // If path is a full URL, return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // If file doesn't exist in storage, check if it's a public file
        if (!\Illuminate\Support\Facades\Storage::disk('uploads')->exists($path)) {
            // Fallback to public path for old files or placeholder images
            return asset($path);
        }
        
        // Return storage URL
        return \Illuminate\Support\Facades\Storage::disk('uploads')->url($path);
    }
}
