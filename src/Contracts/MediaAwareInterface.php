<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface MediaAwareInterface
{
    public function media(): MorphOne;

    public function getPrimaryKey(): int;
}
