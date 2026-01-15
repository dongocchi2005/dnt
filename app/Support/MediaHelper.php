<?php

namespace App\Support;

class MediaHelper
{
    /**
     * Resolve image URL based on path.
     *
     * @param string|null $path
     * @return string
     */
    public static function mediaUrl(?string $path): string
    {
        if (!$path) {
            return asset('images/placeholder.png'); // Or a default image
        }

        // If it's a full URL (http/https)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // If it starts with "/"
        if (str_starts_with($path, '/')) {
            return $path;
        }

        // If file exists in Storage disk 'public'
        if (\Storage::disk('public')->exists($path)) {
            return \Storage::url($path);
        }

        // Otherwise, use asset()
        return asset($path);
    }
}
