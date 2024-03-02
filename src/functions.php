<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

function is_image(?string $filename): bool
{
    return $filename && preg_match('/\.(webp|heic|jpg|jpeg|png)$/i', $filename);
}
