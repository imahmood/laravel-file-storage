<?php
declare(strict_types=1);

return [
    'disk' => env('FILE_STORAGE_DISK', 'media'),
    'queue' => env('FILE_STORAGE_QUEUE', 'media'),
    'max_dimension' => env('FILE_STORAGE_MAX_DIMENSION', 2000),
    'preview_dimension' => env('FILE_STORAGE_PREVIEW_DIMENSION', 300),
];
