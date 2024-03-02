<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use Imahmood\FileStorage\Contracts\ModifierInterface;
use Imahmood\FileStorage\Models\Media;

class Manipulator
{
    /**
     * @var \Imahmood\FileStorage\Contracts\ModifierInterface[]
     */
    protected array $modifiers = [];

    public function addModifier(ModifierInterface $modifier): static
    {
        $this->modifiers[] = $modifier;

        return $this;
    }

    public function applyModifiers(Media $media): Media
    {
        foreach ($this->modifiers as $modifier) {
            if ($modifier->canHandle($media)) {
                $media = $modifier->handle($media);
            }
        }

        return $media;
    }
}
