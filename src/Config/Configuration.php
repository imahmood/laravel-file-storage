<?php

declare(strict_types=1);

namespace Imahmood\FileStorage\Config;

class Configuration
{
    public function __construct(
        public readonly string $diskName,
        public readonly string $queueName,
        public readonly int $maxDimension,
        public readonly int $previewDimension,
    ) {
    }
}
