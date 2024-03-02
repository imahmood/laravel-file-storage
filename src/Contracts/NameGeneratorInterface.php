<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Contracts;

interface NameGeneratorInterface
{
    public function fileName(string $name): string;
}
