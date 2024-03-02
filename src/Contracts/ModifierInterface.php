<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Contracts;

use Imahmood\FileStorage\Models\Media;

interface ModifierInterface
{
    public function canHandle(Media $media): bool;

    public function handle(Media $media): Media;
}
