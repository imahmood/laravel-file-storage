<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Tests\TestSupport\Enums;

use Imahmood\FileStorage\Contracts\MediaTypeInterface;

enum TestDocumentType: int implements MediaTypeInterface
{
    case AVATAR = 1;
    case OTHER = 2;

    public function identifier(): int
    {
        return $this->value;
    }
}
