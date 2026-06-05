<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * Compress and resize an image to a web-friendly size (max 400x400) and save as JPEG.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @return bool
     */
    public static function compressAndResize(string $sourcePath, string $destinationPath, int $maxWidth = 400, int $maxHeight = 400, int $quality = 80): bool
    {
        try {
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }
            
            list($width, $height, $type) = $imageInfo;
            
            // Calculate new dimensions
            $ratio = $width / $height;
            if ($width > $maxWidth || $height > $maxHeight) {
                if ($ratio > 1) {
                    $newWidth = $maxWidth;
                    $newHeight = (int) ($maxWidth / $ratio);
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = (int) ($maxHeight * $ratio);
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            
            // Create image resource based on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $src = @imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $src = @imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $src = @imagecreatefromwebp($sourcePath);
                    } else {
                        return false;
                    }
                    break;
                default:
                    return false; // Unsupported type
            }
            
            if (!$src) {
                return false;
            }
            
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            
            // For profile pictures, we save as JPEG. Fill white background for transparent images.
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Ensure directory exists
            $dir = dirname($destinationPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Save as JPEG
            $success = imagejpeg($dst, $destinationPath, $quality);
            
            imagedestroy($src);
            imagedestroy($dst);
            
            return $success;
        } catch (\Throwable $e) {
            Log::error("ImageHelper error: " . $e->getMessage());
            return false;
        }
    }
}
