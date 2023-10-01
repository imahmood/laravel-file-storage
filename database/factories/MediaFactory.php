<?php

declare(strict_types=1);

namespace Imahmood\FileStorage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Imahmood\FileStorage\Models\Media;

class MediaFactory extends Factory
{
    /**
     * @var class-string<\Imahmood\FileStorage\Models\Media>
     */
    protected $model = Media::class;

    /**
     * @throws \Exception
     */
    public function definition(): array
    {
        return [
            'model_type' => 'App/Models/User',
            'model_id' => 1,
            'file_name' => 'fake-file.jpg',
            'type' => 1,
        ];
    }
}
