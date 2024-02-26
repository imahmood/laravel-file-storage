<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Config\Configuration;
use Imahmood\FileStorage\Contracts\MediaAwareInterface;
use Imahmood\FileStorage\Contracts\MediaTypeInterface;
use Imahmood\FileStorage\Events\AfterMediaSaved;
use Imahmood\FileStorage\Events\AfterMediaUploaded;
use Imahmood\FileStorage\Exceptions\DeleteDirectoryException;
use Imahmood\FileStorage\Exceptions\DeleteFileException;
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Exceptions\UploadException;
use Imahmood\FileStorage\Jobs\GeneratePreview;
use Imahmood\FileStorage\Jobs\OptimizeImage;
use Imahmood\FileStorage\Models\Media;

class FileStorage
{
    protected ?string $disk = null;

    public function __construct(
        protected readonly Configuration $config,
    ) {
    }

    /**
     * Specify the storage disk.
     */
    public function onDisk(string $disk): static
    {
        $this->disk = $disk;

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
            'disk' => $this->disk ?? $this->config->diskName,
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
                $this->deleteFile($media->disk, $originalPaths);
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
                $media->file_name = $uploadedFile->getClientOriginalName();
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

                $this->dispatchJobs($media);

                AfterMediaUploaded::dispatch($media);
            }

            AfterMediaSaved::dispatch($media);

            return $media;
        });
    }

    /**
     * Dispatches jobs for optimizing and generating preview.
     */
    protected function dispatchJobs(Media $media): void
    {
        $jobs = [];

        if ($media->is_image) {
            $jobs[] = new OptimizeImage($media);
        }

        if ($this->config->generatePreview && ($media->is_image || $media->is_pdf)) {
            $jobs[] = new GeneratePreview($media);
        }

        if ($jobs) {
            Bus::chain($jobs)->onQueue($this->config->queueName)->dispatch();
        }
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

            $this->deleteDirectory($media->disk, $media->dir_relative_path);

            return true;
        });
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteDirectoryException
     */
    protected function deleteDirectory(string $disk, string $dir): void
    {
        $isDeleted = Storage::disk($disk)->deleteDirectory($dir);
        if (! $isDeleted) {
            throw new DeleteDirectoryException(sprintf(
                '[FileStorage] Disk: %s, Directory: %s',
                $disk,
                $dir,
            ));
        }
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteFileException
     */
    protected function deleteFile(string $disk, array|string $paths): void
    {
        $isDeleted = Storage::disk($disk)->delete($paths);
        if (! $isDeleted) {
            $paths = is_array($paths) ? implode(', ', $paths) : $paths;

            throw new DeleteFileException(sprintf(
                '[FileStorage] Disk: %s, Paths: %s',
                $disk,
                $paths,
            ));
        }
    }
}
