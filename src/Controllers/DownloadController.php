<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Config\Configuration;
use Imahmood\FileStorage\Exceptions\FileNotFoundException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * @throws \Imahmood\FileStorage\Exceptions\FileNotFoundException
     */
    public function __invoke(Configuration $config, int $mediaId, string $fileName): BinaryFileResponse
    {
        $path = $mediaId.DIRECTORY_SEPARATOR.$fileName;

        if (! Storage::disk($config->diskName)->exists($path)) {
            throw new FileNotFoundException();
        }

        return response()->file(
            Storage::disk($config->diskName)->path($path)
        );
    }
}
