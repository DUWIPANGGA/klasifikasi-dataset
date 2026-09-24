<?php

namespace App\Support;

class GoogleDriveHelper
{
    /**
     * Determine if a given URL is a Google Drive URL.
     */
    public static function isGoogleDriveUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return (bool) preg_match('/(?:drive\.google\.com|docs\.google\.com|googleusercontent\.com)/i', $url);
    }

    /**
     * Extract the Google Drive file ID from a URL or raw ID string.
     */
    public static function extractFileId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Pattern 1: /file/d/{id} or /d/{id} (e.g. drive.google.com/file/d/XYZ/view, lh3.googleusercontent.com/d/XYZ)
        if (preg_match('/(?:\/file\/d\/|\/d\/)([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return $matches[1];
        }

        // Pattern 2: id={id} parameter in query string (e.g. drive.google.com/open?id=XYZ or /uc?id=XYZ or /thumbnail?id=XYZ)
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return $matches[1];
        }

        // Pattern 3: Raw Google Drive ID (typically 25-45 characters alphanumeric with dashes/underscores)
        if (preg_match('/^[a-zA-Z0-9_-]{25,50}$/', trim($url))) {
            return trim($url);
        }

        return null;
    }

    /**
     * Convert any Google Drive URL or File ID into a direct image stream URL.
     */
    public static function toDirectImageUrl(?string $urlOrId, ?int $width = null): ?string
    {
        if (empty($urlOrId)) {
            return $urlOrId;
        }

        $fileId = self::extractFileId($urlOrId);
        if (!$fileId) {
            return $urlOrId;
        }

        if ($width !== null && $width > 0) {
            return "https://lh3.googleusercontent.com/d/{$fileId}=w{$width}";
        }

        return "https://lh3.googleusercontent.com/d/{$fileId}";
    }

    /**
     * Convert any Google Drive URL or File ID into a direct thumbnail URL.
     */
    public static function toThumbnailUrl(?string $urlOrId, int $size = 400): ?string
    {
        if (empty($urlOrId)) {
            return $urlOrId;
        }

        $fileId = self::extractFileId($urlOrId);
        if (!$fileId) {
            return $urlOrId;
        }

        return "https://lh3.googleusercontent.com/d/{$fileId}=w{$size}";
    }

    /**
     * Generate an appropriate filename for a Google Drive URL if default basename is unhelpful (like 'view' or empty).
     */
    public static function getSafeFilename(?string $url, string $defaultExtension = 'jpg'): string
    {
        $fileId = self::extractFileId($url);
        if ($fileId) {
            return "gdrive_{$fileId}.{$defaultExtension}";
        }

        $path = parse_url($url ?? '', PHP_URL_PATH);
        $basename = $path ? basename($path) : '';

        if (!empty($basename) && !in_array(strtolower($basename), ['view', 'edit', 'd', 'uc', 'open', 'thumbnail']) && !str_contains($basename, '?')) {
            // Ensure extension exists
            if (!pathinfo($basename, PATHINFO_EXTENSION)) {
                $basename .= '.' . $defaultExtension;
            }
            return $basename;
        }

        return 'image_' . time() . '.' . $defaultExtension;
    }
}
