<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Imahmood\FileStorage\Config\Configuration;
use Imahmood\FileStorage\Exceptions\NotWritableException;
use Imahmood\FileStorage\Models\Media;
use Jcupitt\Vips\Image as VipsImage;

class FileManipulator
{
    public function __construct(
        private readonly FileStorage $fileStorage,
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
            sourceFile: $media->original_absolute_path,
            targetFile: $media->original_absolute_path,
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

        $previewName = $this->generatePreviewName($media->file_name);

        $this->resizeImage(
            sourceFile: $media->original_absolute_path,
            targetFile: $media->dir_absolute_path.$previewName,
            maxDimension: $this->config->previewDimension,
        );

        $this->fileStorage->updatePreviewName($media, $previewName);
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
    protected function resizeImage(string $sourceFile, string $targetFile, int $maxDimension): void
    {
        $image = VipsImage::newFromFile($sourceFile);

        $width = min($image->width, $maxDimension);
        $height = min($image->height, $maxDimension);
        $fileExt = pathinfo($targetFile, PATHINFO_EXTENSION);

        $saved = file_put_contents(
            $targetFile,
            $image->thumbnail_image($width, ['height' => $height])->writeToBuffer('.'.$fileExt)
        );

        $image = null;

        if ($saved === false) {
            throw new NotWritableException("Can't write image data to path `$targetFile`");
        }
    }
}
