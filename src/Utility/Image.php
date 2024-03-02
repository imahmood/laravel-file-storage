<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Utility;

use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Exceptions\NotWritableException;
use Jcupitt\Vips\Image as VipsImage;

class Image
{
    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function resize(string $disk, string $sourceFile, string $targetFile, int $width, int $height): void
    {
        $image = VipsImage::newFromBuffer(
            Storage::disk($disk)->get($sourceFile)
        );

        $width = min($image->width, $width);
        $height = min($image->height, $height);
        $ext = pathinfo($targetFile, PATHINFO_EXTENSION);

        $saved = Storage::disk($disk)->put(
            path: $targetFile,
            contents: $image->thumbnail_image($width, ['height' => $height])->writeToBuffer('.'.$ext),
        );

        $image = null;

        if ($saved === false) {
            throw new NotWritableException("Can't write image data to path `$targetFile`");
        }
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function convert(string $disk, string $sourceFile, string $targetFile): void
    {
        $image = VipsImage::newFromBuffer(
            Storage::disk($disk)->get($sourceFile)
        );

        $ext = pathinfo($targetFile, PATHINFO_EXTENSION);

        $saved = Storage::disk($disk)->put(
            path: $targetFile,
            contents: $image->writeToBuffer('.'.$ext),
        );

        $image = null;

        if ($saved === false) {
            throw new NotWritableException("Can't write image data to path `$targetFile`");
        }
    }
}
