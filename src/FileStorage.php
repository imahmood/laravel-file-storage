<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Imahmood\FileStorage\Contracts\MediaAwareInterface;
use Imahmood\FileStorage\Contracts\MediaTypeInterface;
use Imahmood\FileStorage\Contracts\NameGeneratorInterface;
use Imahmood\FileStorage\Events\AfterMediaSaved;
use Imahmood\FileStorage\Events\AfterMediaUploaded;
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Exceptions\UploadException;
use Imahmood\FileStorage\Jobs\RunModifiersJob;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Utility\Filesystem;

class FileStorage
{
    protected string $diskName;

    protected string $queueName;

    protected bool $queueModifiers;

    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly Manipulator $manipulator,
        protected readonly NameGeneratorInterface $nameGenerator,
    ) {
        $this->diskName = config('file-storage.disk');
        $this->queueName = config('file-storage.queue');
        $this->queueModifiers = config('file-storage.queue_modifiers');
    }

    /**
     * Specify the storage disk.
     */
    public function onDisk(string $disk): static
    {
        $this->diskName = $disk;

        return $this;
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     */
    public function create(
        MediaTypeInterface $type,
        ?MediaAwareInterface $relatedTo,
        UploadedFile $uploadedFile,
    ): Media {
        $media = new Media([
            'disk' => $this->diskName,
            'model_type' => $relatedTo ? $relatedTo::class : null,
            'model_id' => $relatedTo?->getPrimaryKey(),
            'type' => $type->identifier(),
        ]);

        return $this->persistMedia($media, $uploadedFile);
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Imahmood\FileStorage\Exceptions\DeleteFileException
     */
    public function update(
        MediaTypeInterface $type,
        ?MediaAwareInterface $relatedTo,
        Media $media,
        ?UploadedFile $uploadedFile,
    ): Media {
        $media->fill([
            'model_type' => $relatedTo ? $relatedTo::class : null,
            'model_id' => $relatedTo?->getPrimaryKey(),
            'type' => $type->identifier(),
        ]);

        return DB::transaction(function () use ($media, $uploadedFile) {
            $originalPaths = array_filter([
                $media->original_relative_path,
                $media->preview_relative_path,
            ]);

            $media = $this->persistMedia($media, $uploadedFile);

            if ($uploadedFile) {
                $this->filesystem->deleteFile($media->disk, $originalPaths);
            }

            return $media;
        });
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Imahmood\FileStorage\Exceptions\DeleteFileException
     */
    public function updateOrCreate(
        MediaTypeInterface $type,
        ?MediaAwareInterface $relatedTo,
        ?Media $media,
        UploadedFile $uploadedFile,
    ): Media {
        if ($media) {
            return $this->update($type, $relatedTo, $media, $uploadedFile);
        }

        return $this->create($type, $relatedTo, $uploadedFile);
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     */
    protected function persistMedia(Media $media, ?UploadedFile $uploadedFile): Media
    {
        return DB::transaction(function () use ($media, $uploadedFile) {
            if ($uploadedFile) {
                $media->file_name = $this->nameGenerator->fileName($uploadedFile->getClientOriginalName());
                $media->preview = null;
            }

            if (! $media->save()) {
                throw new PersistenceFailedException();
            }

            if ($uploadedFile) {
                $isUploaded = (bool) $uploadedFile->storeAs($media->dir_relative_path, $media->file_name, [
                    'disk' => $media->disk,
                ]);

                if (! $isUploaded) {
                    throw new UploadException();
                }

                if ($this->queueModifiers) {
                    RunModifiersJob::dispatch($media)->onQueue($this->queueName);
                } else {
                    $media = $this->manipulator->applyModifiers($media);
                }

                AfterMediaUploaded::dispatch($media);
            }

            AfterMediaSaved::dispatch($media);

            return $media;
        });
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteDirectoryException
     */
    public function delete(Media $media): bool
    {
        return DB::transaction(function () use ($media) {
            if (! $media->delete()) {
                return false;
            }

            if (config("filesystems.disks.$media->disk.driver") === 's3') {
                $this->filesystem->deleteFile($media->disk, array_filter([
                    $media->original_relative_path,
                    $media->preview_relative_path,
                ]));
            } else {
                $this->filesystem->deleteDirectory($media->disk, $media->dir_relative_path);
            }

            return true;
        });
    }
}
