<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Get the correct asset path for CSS and JS files
     * Automatically detects if we're in development or production
     */
    public static function getAssetPath($file)
    {
        $manifestPath = public_path('build/manifest.json');
        
        // If manifest exists, we're in production mode
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            if (isset($manifest[$file])) {
                return asset('build/' . $manifest[$file]['file']);
            }
        }
        
        // Fallback for development or if manifest doesn't exist
        return asset($file);
    }
    
    /**
     * Get CSS asset path
     */
    public static function css($file = 'resources/css/app.css')
    {
        return self::getAssetPath($file);
    }
    
    /**
     * Get JS asset path
     */
    public static function js($file = 'resources/js/app.js')
    {
        return self::getAssetPath($file);
    }
    
    /**
     * Check if we're in production mode (has compiled assets)
     */
    public static function isProduction()
    {
        return file_exists(public_path('build/manifest.json'));
    }
}