<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Config\Configuration;
use Imahmood\FileStorage\Exceptions\NotWritableException;
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Models\Media;
use Jcupitt\Vips\Image as VipsImage;

class FileManipulator
{
    public function __construct(
        private readonly Configuration $config,
    ) {
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function optimizeImage(Media $media): void
    {
        if (! $media->is_image) {
            return;
        }

        $this->resizeImage(
            media: $media,
            targetFile: $media->original_relative_path,
            maxDimension: $this->config->maxDimension,
        );
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Jcupitt\Vips\Exception
     */
    public function generatePreview(Media $media): void
    {
        if (! $media->is_image && ! $media->is_pdf) {
            return;
        }

        $media->preview = $this->generatePreviewName($media->file_name);

        $this->resizeImage(
            media: $media,
            targetFile: $media->dir_relative_path.$media->preview,
            maxDimension: $this->config->previewDimension,
        );

        if (! $media->save()) {
            throw new PersistenceFailedException();
        }
    }

    protected function generatePreviewName(string $fileName): string
    {
        return sprintf(
            '%s-preview.jpg',
            pathinfo($fileName, PATHINFO_FILENAME)
        );
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    protected function resizeImage(Media $media, string $targetFile, int $maxDimension): void
    {
        $image = VipsImage::newFromBuffer(
            Storage::disk($media->disk)->get($media->original_relative_path)
        );

        $width = min($image->width, $maxDimension);
        $height = min($image->height, $maxDimension);
        $fileExt = pathinfo($targetFile, PATHINFO_EXTENSION);

        $saved = Storage::disk($media->disk)->put(
            path: $targetFile,
            contents: $image->thumbnail_image($width, ['height' => $height])->writeToBuffer('.'.$fileExt),
        );

        $image = null;

        if ($saved === false) {
            throw new NotWritableException("Can't write image data to path `$targetFile`");
        }
    }
}
