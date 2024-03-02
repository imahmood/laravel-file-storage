<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Tests;

use Imahmood\FileStorage\FileStorage;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Tests\TestSupport\Enums\TestDocumentType;
use Imahmood\FileStorage\Tests\TestSupport\Models\TestUserModel;
use Imahmood\FileStorage\Tests\TestSupport\UploadedFile;

class FileStorageTest extends TestCase
{
    public function testCreateMedia(): void
    {
        $fileStorage = app(FileStorage::class);
        $media = $fileStorage->create(
            type: TestDocumentType::AVATAR,
            relatedTo: new TestUserModel(),
            uploadedFile: UploadedFile::fake('jpg'),
        );

        $this->assertNull($media->preview);
        $this->assertSame($media->type, TestDocumentType::AVATAR->value);
        $this->assertStringEndsWith('.jpg', $media->file_name);
    }

    public function testUpdateMedia(): void
    {
        /** @var \Imahmood\FileStorage\Models\Media $originalMedia */
        $originalMedia = Media::factory()->create([
            'model_type' => 'App/Models/User',
            'model_id' => 1,
            'file_name' => 'fake-file.JPG',
            'type' => TestDocumentType::AVATAR,
        ]);

        $fileStorage = app(FileStorage::class);
        $updatedMedia = $fileStorage->update(
            type: TestDocumentType::OTHER,
            relatedTo: new TestUserModel(),
            media: clone $originalMedia,
            uploadedFile: UploadedFile::fake('jpg'),
        );

        $this->assertNotSame($updatedMedia->file_name, $originalMedia->file_name);
        $this->assertNull($updatedMedia->preview);
        $this->assertSame($updatedMedia->type, TestDocumentType::OTHER->value);
        $this->assertStringEndsWith('.jpg', $updatedMedia->file_name);
    }

    public function testDeleteMedia(): void
    {
        /** @var \Imahmood\FileStorage\Models\Media $media */
        $media = Media::factory()->create();

        $fileStorage = app(FileStorage::class);
        $result = $fileStorage->delete($media);

        $this->assertTrue($result);
    }
}
