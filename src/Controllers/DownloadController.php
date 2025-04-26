<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Exceptions\FileNotFoundException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * @throws \Imahmood\FileStorage\Exceptions\FileNotFoundException
     */
    public function __invoke(string $disk, string $path): BinaryFileResponse
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw new FileNotFoundException;
        }

        return response()->file(
            Storage::disk($disk)->path($path)
        );
    }
}
