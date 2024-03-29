<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Imahmood\FileStorage\Models\Media;

/**
 * @property-read \Imahmood\FileStorage\Models\Media|null $media
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait MediaAwareTrait
{
    public function media(): MorphOne
    {
        return $this
            ->morphOne(Media::class, 'model')
            ->orderByDesc('id');
    }

    public function getPrimaryKey(): int
    {
        return $this->getAttribute($this->getKeyName());
    }
}
