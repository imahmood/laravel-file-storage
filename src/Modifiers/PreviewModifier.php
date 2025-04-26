<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Modifiers;

use Imahmood\FileStorage\Contracts\ModifierInterface;
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Utility\Filesystem;
use Imahmood\FileStorage\Utility\Image;

class PreviewModifier implements ModifierInterface
{
    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly Image $image,
        protected readonly array $options,
    ) {}

    public function canHandle(Media $media): bool
    {
        return $media->is_image || $media->is_pdf;
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Jcupitt\Vips\Exception
     */
    public function handle(Media $media): Media
    {
        $oldPreview = $media->preview_relative_path;
        $media->preview = $this->generateName($media->file_name);

        $this->image->resize(
            disk: $media->disk,
            sourceFile: $media->original_relative_path,
            targetFile: $media->dir_relative_path.$media->preview,
            width: $this->options['width'],
            height: $this->options['height'],
        );

        if (! $media->save()) {
            throw new PersistenceFailedException;
        }

        if ($oldPreview) {
            $this->filesystem->deleteFile($media->disk, $oldPreview);
        }

        return $media;
    }

    protected function generateName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME).'-preview.'.$this->options['format'];
    }
}
