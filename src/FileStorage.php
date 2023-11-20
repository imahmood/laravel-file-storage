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
use Imahmood\FileStorage\Exceptions\PersistenceFailedException;
use Imahmood\FileStorage\Exceptions\UnableToDeleteDirectoryException;
use Imahmood\FileStorage\Exceptions\UnableToDeleteFileException;
use Imahmood\FileStorage\Jobs\GeneratePreview;
use Imahmood\FileStorage\Jobs\OptimizeImage;
use Imahmood\FileStorage\Models\Media;

class FileStorage
{
    public function __construct(
        protected readonly Configuration $config,
    ) {
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
            'model_type' => $relatedTo ? $relatedTo::class : null,
            'model_id' => $relatedTo?->getPrimaryKey(),
            'type' => $type->identifier(),
        ]);

        return $this->persistMedia($media, $uploadedFile);
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Imahmood\FileStorage\Exceptions\UnableToDeleteFileException
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
                $this->deleteFile($originalPaths);
            }

            return $media;
        });
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Imahmood\FileStorage\Exceptions\PersistenceFailedException
     * @throws \Imahmood\FileStorage\Exceptions\UnableToDeleteFileException
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
    public function updatePreviewName(Media $media, string $fileName): Media
    {
        $media->preview = $fileName;

        if (! $media->save()) {
            throw new PersistenceFailedException();
        }

        return $media;
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
                    'disk' => $this->config->diskName,
                ]);

                if (! $isUploaded) {
                    throw new PersistenceFailedException();
                }

                if ($media->is_image) {
                    Bus::chain([
                        new OptimizeImage($media),
                        new GeneratePreview($media),
                    ])->onQueue($this->config->queueName)->dispatch();
                } elseif ($media->is_pdf) {
                    dispatch(new GeneratePreview($media))->onQueue($this->config->queueName);
                }
            }

            return $media;
        });
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\UnableToDeleteDirectoryException
     */
    public function delete(Media $media): bool
    {
        return DB::transaction(function () use ($media) {
            if (! $media->delete()) {
                return false;
            }

            $this->deleteDirectory($media->dir_relative_path);

            return true;
        });
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\UnableToDeleteDirectoryException
     */
    protected function deleteDirectory(string $dir): void
    {
        $isDeleted = Storage::disk($this->config->diskName)->deleteDirectory($dir);
        if (! $isDeleted) {
            throw new UnableToDeleteDirectoryException(sprintf(
                '[FileStorage] Disk: %s, Directory: %s',
                $this->config->diskName,
                $dir
            ));
        }
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\UnableToDeleteFileException
     */
    protected function deleteFile(array|string $paths): void
    {
        $isDeleted = Storage::disk($this->config->diskName)->delete($paths);
        if (! $isDeleted) {
            $paths = is_array($paths) ? implode(', ', $paths) : $paths;

            throw new UnableToDeleteFileException(sprintf(
                '[FileStorage] Disk: %s, Paths: %s',
                $this->config->diskName,
                $paths
            ));
        }
    }
}
