<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Imahmood\FileStorage\Manipulator;
use Imahmood\FileStorage\Models\Media;

class RunModifiersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Media $media)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Manipulator $manipulator): void
    {
        $manipulator->applyModifiers($this->media);
    }
}
