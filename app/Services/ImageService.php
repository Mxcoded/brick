<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Maximum file size in bytes (5MB)
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * Maximum dimension (width or height) for resizing
     */
    protected int $maxDimension = 2000;

    /**
     * Starting quality for compression
     */
    protected int $startQuality = 90;

    /**
     * Minimum quality threshold
     */
    protected int $minQuality = 60;

    /**
     * Compress and store an uploaded image
     *
     * @param  UploadedFile  $file  The uploaded image file
     * @param  string  $directory  Storage directory (e.g., 'rooms', 'room_gallery')
     * @param  string  $disk  Storage disk (default: 'public')
     * @return array ['path' => string, 'url' => string, 'size' => int]
     */
    public function compressAndStore(UploadedFile $file, string $directory, string $disk = 'public'): array
    {
        // Read the image using Intervention
        $image = Image::read($file);

        // Get original dimensions
        $width = $image->width();
        $height = $image->height();

        // Resize if larger than max dimension (maintain aspect ratio)
        if ($width > $this->maxDimension || $height > $this->maxDimension) {
            $image = $image->scaleDown($this->maxDimension, $this->maxDimension);
        }

        // Determine output format and encoder
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->generateFilename($file->getClientOriginalName(), $extension);

        // Compress with adaptive quality
        $encodedImage = $this->compressToTargetSize($image, $extension);

        // Store the compressed image
        $path = $directory.'/'.$filename;
        Storage::disk($disk)->put($path, $encodedImage);

        return [
            'path' => $path,
            'url' => Storage::url($path),
            'size' => strlen($encodedImage),
        ];
    }

    /**
     * Compress image to target size with adaptive quality
     *
     * @param  ImageInterface  $image
     * @return string Encoded image data
     */
    protected function compressToTargetSize($image, string $extension): string
    {
        $quality = $this->startQuality;

        // For PNG, convert to JPEG for better compression (unless transparency is needed)
        // We'll use WebP as a fallback for better compression
        $useJpeg = in_array($extension, ['jpg', 'jpeg', 'png', 'bmp']);

        do {
            if ($useJpeg) {
                $encoded = $image->encode(new JpegEncoder(quality: $quality));
            } else {
                // For other formats, try WebP
                $encoded = $image->encode(new WebpEncoder(quality: $quality));
            }

            $encodedString = (string) $encoded;
            $size = strlen($encodedString);

            // If size is within limit, we're done
            if ($size <= $this->maxFileSize) {
                return $encodedString;
            }

            // Reduce quality and try again
            $quality -= 5;

        } while ($quality >= $this->minQuality);

        // If still too large after quality reduction, resize further
        if ($size > $this->maxFileSize) {
            $currentWidth = $image->width();
            $newWidth = (int) ($currentWidth * 0.8); // Reduce by 20%
            $image = $image->scaleDown($newWidth);

            // Try encoding again at minimum quality
            if ($useJpeg) {
                $encoded = $image->encode(new JpegEncoder(quality: $this->minQuality));
            } else {
                $encoded = $image->encode(new WebpEncoder(quality: $this->minQuality));
            }

            return (string) $encoded;
        }

        return $encodedString;
    }

    /**
     * Generate a unique filename
     */
    protected function generateFilename(string $originalName, string $extension): string
    {
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($baseName);
        $timestamp = now()->format('YmdHis');
        $random = Str::random(6);

        // Always save as JPEG for consistent compression
        $outputExtension = in_array($extension, ['png', 'bmp', 'gif']) ? 'jpg' : $extension;

        return "{$safeName}_{$timestamp}_{$random}.{$outputExtension}";
    }

    /**
     * Set maximum file size in MB
     */
    public function setMaxFileSize(float $mb): self
    {
        $this->maxFileSize = (int) ($mb * 1024 * 1024);

        return $this;
    }

    /**
     * Set maximum dimension
     */
    public function setMaxDimension(int $pixels): self
    {
        $this->maxDimension = $pixels;

        return $this;
    }

    /**
     * Delete an image from storage
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete image by URL (extracts path from URL)
     */
    public function deleteByUrl(string $url, string $disk = 'public'): bool
    {
        $path = str_replace('/storage/', '', $url);

        return $this->delete($path, $disk);
    }
}
