<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Imahmood\FileStorage\FileManipulator;
use Imahmood\FileStorage\Models\Media;

class OptimizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private readonly Media $media)
    {
    }

    /**
     * Execute the job.
     *
     * @throws \Imahmood\FileStorage\Exceptions\NotWritableException
     * @throws \Jcupitt\Vips\Exception
     */
    public function handle(FileManipulator $fileManipulator): void
    {
        $fileManipulator->optimizeImage($this->media);
    }
}
