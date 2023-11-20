<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Contracts;

interface MediaTypeInterface
{
    /**
     * Return a unique identifier for the media type.
     */
    public function identifier(): int;
}
