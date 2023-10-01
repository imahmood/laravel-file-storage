<?php

declare(strict_types=1);

namespace Imahmood\FileStorage\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Imahmood\FileStorage\Contracts\MediaAwareInterface;
use Imahmood\FileStorage\MediaAwareTrait;

class TestUserModel extends Model implements MediaAwareInterface
{
    use MediaAwareTrait;

    public function getPrimaryKey(): int
    {
        return 1001;
    }
}
