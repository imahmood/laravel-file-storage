<?php
declare(strict_types=1);

use Imahmood\FileStorage\Modifiers\DownscaleModifier;
use Imahmood\FileStorage\Modifiers\FormatModifier;
use Imahmood\FileStorage\Modifiers\PreviewModifier;
use Imahmood\FileStorage\NameGenerator;

return [
    'disk' => env('FILE_STORAGE_DISK', 'local'),
    'queue' => env('FILE_STORAGE_QUEUE', 'media'),
    'name_generator' => NameGenerator::class,
    'queue_modifiers' => env('FILE_STORAGE_QUEUE_MODIFIERS', true),
    'modifiers' => [
        FormatModifier::class => [
            'formats' => [
                'png' => 'jpg',
                'webp' => 'jpg',
                'jfif' => 'jpg',
                'heic' => 'jpg',
            ],
            'flatten' => true,
        ],
        DownscaleModifier::class => [
            'width' => 2000,
            'height' => 2000,
        ],
        PreviewModifier::class => [
            'width' => 150,
            'height' => 150,
            'format' => 'jpg',
            'flatten' => true,
        ],
    ],
];
