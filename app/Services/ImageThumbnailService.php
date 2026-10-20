<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageThumbnailService
{
    /**
     * Generate an optimized thumbnail on the 'public' disk using native PHP GD.
     * Supports JPEG, PNG, and WebP preserving aspect ratio and alpha transparency.
     * Returns the same relative path if the source is an SVG.
     */
    public function generateThumbnail(string $sourceRelativePath, int $maxWidth = 400, int $maxHeight = 400): ?string
    {
        $sourceRelativePath = ltrim(str_replace('\\', '/', trim($sourceRelativePath)), '/');

        if ($sourceRelativePath === '') {
            return null;
        }

        $extension = strtolower(pathinfo($sourceRelativePath, PATHINFO_EXTENSION));
        if ($extension === 'svg') {
            return $sourceRelativePath;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($sourceRelativePath)) {
            return null;
        }

        if (! extension_loaded('gd')) {
            return $sourceRelativePath;
        }

        $sourceFullPath = $disk->path($sourceRelativePath);

        try {
            $imageInfo = @getimagesize($sourceFullPath);
            if ($imageInfo === false) {
                return $sourceRelativePath;
            }

            [$origWidth, $origHeight, $imageType] = $imageInfo;
            if ($origWidth <= 0 || $origHeight <= 0) {
                return $sourceRelativePath;
            }

            $srcImage = match ($imageType) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($sourceFullPath),
                IMAGETYPE_PNG => @imagecreatefrompng($sourceFullPath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourceFullPath) : false,
                default => false,
            };

            if (! $srcImage) {
                return $sourceRelativePath;
            }

            // Calculate proportional dimensions without upscaling smaller images
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1.0);
            $targetWidth = max(1, (int) round($origWidth * $ratio));
            $targetHeight = max(1, (int) round($origHeight * $ratio));

            $thumbImage = imagecreatetruecolor($targetWidth, $targetHeight);
            if (! $thumbImage) {
                imagedestroy($srcImage);

                return $sourceRelativePath;
            }

            // Preserve transparency for PNG and WebP
            if (in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                imagealphablending($thumbImage, false);
                imagesavealpha($thumbImage, true);
                $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
                imagefilledrectangle($thumbImage, 0, 0, $targetWidth, $targetHeight, $transparent);
            } else {
                $white = imagecolorallocate($thumbImage, 255, 255, 255);
                imagefilledrectangle($thumbImage, 0, 0, $targetWidth, $targetHeight, $white);
            }

            imagecopyresampled(
                $thumbImage,
                $srcImage,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            $disk->makeDirectory('products/thumbs');

            $basename = pathinfo($sourceRelativePath, PATHINFO_FILENAME);
            $extOut = match ($imageType) {
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                default => 'jpg',
            };

            $thumbRelativePath = sprintf('products/thumbs/%s_thumb.%s', $basename, $extOut);
            $thumbFullPath = $disk->path($thumbRelativePath);

            $saved = match ($imageType) {
                IMAGETYPE_PNG => imagepng($thumbImage, $thumbFullPath, 7),
                IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($thumbImage, $thumbFullPath, 82) : imagejpeg($thumbImage, $thumbFullPath, 85),
                default => imagejpeg($thumbImage, $thumbFullPath, 85),
            };

            imagedestroy($srcImage);
            imagedestroy($thumbImage);

            return $saved ? $thumbRelativePath : $sourceRelativePath;
        } catch (Throwable) {
            return $sourceRelativePath;
        }
    }
}
