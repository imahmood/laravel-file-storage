<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Imahmood\FileStorage\Models\Media;

class AfterMediaSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Media $media,
    ) {
    }
}
