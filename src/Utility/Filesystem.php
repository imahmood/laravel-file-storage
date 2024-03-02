<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Utility;

use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Exceptions\DeleteDirectoryException;
use Imahmood\FileStorage\Exceptions\DeleteFileException;

class Filesystem
{
    /**
     * @throws \Imahmood\FileStorage\Exceptions\DeleteDirectoryException
     */
    public function deleteDirectory(string $disk, string $dir): void
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
    public function deleteFile(string $disk, array|string $paths): void
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
