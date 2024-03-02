<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Imahmood\FileStorage\Contracts\NameGeneratorInterface;

class NameGenerator implements NameGeneratorInterface
{
    public function fileName(string $name): string
    {
        $fileName = sha1($name.microtime());
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return "$fileName.$ext";
    }
}
