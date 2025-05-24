<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    protected const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    protected const MAX_FILE_SIZE = 5242880; // 5MB
    protected const MAX_DIMENSION = 2048; // 2048px

    public function upload(UploadedFile $file, string $path = 'posts'): array
    {
        $this->validate($file);

        $filename = $this->generateFilename($file);
        $fullPath = "{$path}/{$filename}";

        // Process and store the image
        $image = Image::make($file);

        // Resize if needed
        if ($image->width() > self::MAX_DIMENSION || $image->height() > self::MAX_DIMENSION) {
            $image->resize(self::MAX_DIMENSION, self::MAX_DIMENSION, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Store the processed image
        Storage::disk('public')->put($fullPath, $image->encode());

        return [
            'filename' => $filename,
            'path' => $fullPath,
            'url' => Storage::disk('public')->url($fullPath),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'dimensions' => [
                'width' => $image->width(),
                'height' => $image->height()
            ]
        ];
    }

    public function delete(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    protected function validate(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException(
                'Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_MIME_TYPES)
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum limit of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB'
            );
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }
}
