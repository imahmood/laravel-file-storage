<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Utility;

use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Exceptions\NotWritableException;
use Jcupitt\Vips\Image as VipsImage;

class Image
{
    /**
     * Resize the image and save it.
     *
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function resize(string $disk, string $sourceFile, string $targetFile, int $width, int $height, bool $flatten): void
    {
        $image = $this->loadImage($disk, $sourceFile);

        $width = min($image->width, $width);
        $height = min($image->height, $height);

        if ($flatten && $image->hasAlpha()) {
            $image = $image->flatten(['background' => [255, 255, 255]]);
        }

        $image = $image->thumbnail_image($width, ['height' => $height]);
        $this->saveImage($disk, $targetFile, $image);
    }

    /**
     * Convert the image format and save it.
     *
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function convert(string $disk, string $sourceFile, string $targetFile, bool $flatten): void
    {
        $image = $this->loadImage($disk, $sourceFile);

        if ($flatten && $image->hasAlpha()) {
            $image = $image->flatten(['background' => [255, 255, 255]]);
        }

        $this->saveImage($disk, $targetFile, $image);
    }

    /**
     * Load image from storage.
     */
    private function loadImage(string $disk, string $path): VipsImage
    {
        $content = Storage::disk($disk)->get($path);

        return VipsImage::newFromBuffer($content);
    }

    /**
     * Save image to storage.
     *
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     */
    private function saveImage(string $disk, string $path, VipsImage $image): void
    {
        $extension = '.'.pathinfo($path, PATHINFO_EXTENSION);
        $buffer = $image->writeToBuffer($extension);

        if (! Storage::disk($disk)->put($path, $buffer)) {
            throw new NotWritableException("Can't write image data to path `$path`");
        }
    }
}
