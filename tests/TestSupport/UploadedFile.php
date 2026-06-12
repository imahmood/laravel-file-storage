<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Tests\TestSupport;

use Exception;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile as HttpUploadedFile;

class UploadedFile
{
    public static function fake(string $ext): File
    {
        $paths = [
            'jpg' => __DIR__.'/assets/avatar.JPG',
            'heic' => __DIR__.'/assets/sample-heic-image.heic',
        ];

        $fileContent = file_get_contents($paths[$ext]);

        if (! $fileContent) {
            throw new Exception();
        }

        return HttpUploadedFile::fake()->create($paths[$ext], $fileContent);
    }
}
