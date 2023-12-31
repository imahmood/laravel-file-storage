<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Config;

class ConfigurationFactory
{
    public static function create(array $data): Configuration
    {
        return new Configuration(
            diskName: $data['disk'],
            queueName: $data['queue'],
            maxDimension: $data['max_dimension'],
            previewDimension: $data['preview_dimension'],
            generatePreview: (bool) $data['generate_preview'],
        );
    }
}
