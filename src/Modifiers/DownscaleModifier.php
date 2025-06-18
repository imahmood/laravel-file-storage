<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Modifiers;

use Imahmood\FileStorage\Contracts\ModifierInterface;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Utility\Image;

class DownscaleModifier implements ModifierInterface
{
    public function __construct(
        protected Image $image,
        protected array $options,
    ) {}

    public function canHandle(Media $media): bool
    {
        return $media->is_image;
    }

    /**
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function handle(Media $media): Media
    {
        $this->image->resize(
            disk: $media->disk,
            sourceFile: $media->original_relative_path,
            targetFile: $media->original_relative_path,
            width: $this->options['width'],
            height: $this->options['height'],
            flatten: false,
        );

        return $media;
    }
}
