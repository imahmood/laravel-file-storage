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
        protected Filesystem $filesystem,
        protected Image $image,
        protected array $options,
    ) {}

    public function canHandle(Media $media): bool
    {
        if (! $media->is_image) {
            return false;
        }

        [$sourceExt, $targetExt, $flatten] = $this->resolveOptions($media);

        return $targetExt !== $sourceExt || $flatten;
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteFileException
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Jcupitt\Vips\Exception
     */
    public function handle(Media $media): Media
    {
        [, $targetExt, $flatten] = $this->resolveOptions($media);
        $originalFile = $media->original_relative_path;
        $newName = pathinfo($originalFile, PATHINFO_FILENAME).'.'.$targetExt;

        $this->image->convert(
            disk: $media->disk,
            sourceFile: $originalFile,
            targetFile: $media->dir_relative_path.$newName,
            flatten: $flatten,
        );

        $media->file_name = $newName;
        if (! $media->save()) {
            throw new PersistenceFailedException;
        }

        if ($media->wasChanged('file_name')) {
            $this->filesystem->deleteFile($media->disk, $originalFile);
        }

        return $media;
    }

    private function resolveOptions(Media $media): array
    {
        $sourceExt = pathinfo($media->file_name, PATHINFO_EXTENSION);
        $targetExt = $this->options['formats'][$sourceExt] ?? $sourceExt;
        $flatten = $this->options['flatten'] ?? false;

        return [$sourceExt, $targetExt, $flatten];
    }
}
