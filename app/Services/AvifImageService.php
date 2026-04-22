<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AvifImageService
{
    private const AVIF_QUALITY = 70;

    public function storePublicImage(UploadedFile $file, string $directory, ?string $baseName = null, ?string $oldPath = null): string
    {
        $path = $this->buildRelativePath($directory, $baseName ?: $file->getClientOriginalName());

        Storage::disk('public')->put($path, $this->convertToAvif($file));

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    public function savePublicImage(UploadedFile $file, string $directory, ?string $baseName = null, ?string $oldPath = null): string
    {
        $relativePath = $this->buildRelativePath($directory, $baseName ?: $file->getClientOriginalName());
        $absolutePath = public_path($relativePath);

        File::ensureDirectoryExists(dirname($absolutePath));
        file_put_contents($absolutePath, $this->convertToAvif($file));

        if ($oldPath) {
            $oldAbsolutePath = public_path($oldPath);
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }

        return str_replace('\\', '/', $relativePath);
    }

    private function buildRelativePath(string $directory, string $baseName): string
    {
        $normalizedDirectory = trim(str_replace('\\', '/', $directory), '/');
        $slug = Str::slug(pathinfo($baseName, PATHINFO_FILENAME));
        $slug = $slug !== '' ? $slug : 'image';

        return $normalizedDirectory . '/' . now()->format('YmdHis') . '_' . Str::random(8) . '_' . $slug . '.avif';
    }

    private function convertToAvif(UploadedFile $file): string
    {
        if (!function_exists('imageavif')) {
            throw new RuntimeException('AVIF conversion is not available on this server.');
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;

        if ($source === false) {
            throw new RuntimeException('Unable to process the uploaded image.');
        }

        if (!imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }

        imagealphablending($source, true);
        imagesavealpha($source, true);

        ob_start();
        $encoded = imageavif($source, null, self::AVIF_QUALITY);
        $binary = ob_get_clean();

        imagedestroy($source);

        if ($encoded === false || $binary === false) {
            throw new RuntimeException('Failed to encode image as AVIF.');
        }

        return $binary;
    }
}
