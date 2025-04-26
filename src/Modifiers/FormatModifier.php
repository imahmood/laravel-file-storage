<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Modifiers;

use Imahmood\FileStorage\Contracts\ModifierInterface;
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Utility\Filesystem;
use Imahmood\FileStorage\Utility\Image;

class FormatModifier implements ModifierInterface
{
    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly Image $image,
        protected readonly array $options,
    ) {}

    public function canHandle(Media $media): bool
    {
        $formats = array_keys($this->options['formats']);
        $ext = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $media->is_image && in_array($ext, $formats, true);
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteFileException
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Jcupitt\Vips\Exception
     */
    public function handle(Media $media): Media
    {
        $originalFile = $media->original_relative_path;
        $ext = pathinfo($media->file_name, PATHINFO_EXTENSION);
        $newName = pathinfo($originalFile, PATHINFO_FILENAME).'.'.$this->options['formats'][$ext];

        $this->image->convert(
            disk: $media->disk,
            sourceFile: $originalFile,
            targetFile: $media->dir_relative_path.$newName,
        );

        $media->file_name = $newName;
        if (! $media->save()) {
            throw new PersistenceFailedException;
        }

        $this->filesystem->deleteFile($media->disk, $originalFile);

        return $media;
    }
}
