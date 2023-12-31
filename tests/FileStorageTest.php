<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Tests;

use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Imahmood\FileStorage\Config\ConfigurationFactory;
use Imahmood\FileStorage\FileStorage;
use Imahmood\FileStorage\Jobs\GeneratePreview;
use Imahmood\FileStorage\Jobs\OptimizeImage;
use Imahmood\FileStorage\Models\Media;
use Imahmood\FileStorage\Tests\TestSupport\Enums\TestDocumentType;
use Imahmood\FileStorage\Tests\TestSupport\Models\TestUserModel;

class FileStorageTest extends TestCase
{
    private FileStorage $fileStorage;

    private File $testFile;

    protected function setUp(): void
    {
        parent::setUp();

        $config = ConfigurationFactory::create([
            'disk' => 'media',
            'queue' => 'media',
            'max_dimension' => 2000,
            'preview_dimension' => 300,
            'generate_preview' => true,
        ]);

        $this->fileStorage = new FileStorage($config);

        $path = __DIR__.'/TestSupport/assets/avatar.jpg';
        $this->testFile = UploadedFile::fake()->create($path, file_get_contents($path));

        Queue::fake();
        Storage::fake('media');
        Auth::partialMock()->shouldReceive('id')->zeroOrMoreTimes()->andReturn(1);
    }

    public function testCreateMedia(): void
    {
        $media = $this->fileStorage->create(
            type: TestDocumentType::AVATAR,
            relatedTo: new TestUserModel(),
            uploadedFile: $this->testFile,
        );

        $this->assertNull($media->preview);
        $this->assertSame($media->type, TestDocumentType::AVATAR->value);

        Queue::assertPushedWithChain(OptimizeImage::class, [
            GeneratePreview::class,
        ]);
    }

    public function testUpdateMedia(): void
    {
        /** @var \Imahmood\FileStorage\Models\Media $originalMedia */
        $originalMedia = Media::factory()->create([
            'model_type' => 'App/Models/User',
            'model_id' => 1,
            'file_name' => 'fake-file.jpg',
            'type' => TestDocumentType::AVATAR,
        ]);

        $relatedModel = new TestUserModel();

        $updatedMedia = $this->fileStorage->update(
            type: TestDocumentType::OTHER,
            relatedTo: $relatedModel,
            media: clone $originalMedia,
            uploadedFile: $this->testFile,
        );

        $this->assertNotSame($updatedMedia->file_name, $originalMedia->file_name);
        $this->assertNull($updatedMedia->preview);
        $this->assertSame($updatedMedia->type, TestDocumentType::OTHER->value);

        Queue::assertPushedWithChain(OptimizeImage::class, [
            GeneratePreview::class,
        ]);
    }

    public function testUpdatePreviewName(): void
    {
        /** @var \Imahmood\FileStorage\Models\Media $originalMedia */
        $originalMedia = Media::factory()->create();

        $updatedMedia = $this->fileStorage->updatePreviewName(clone $originalMedia, 'random-name.jpg');

        $this->assertSame($updatedMedia->file_name, $originalMedia->file_name);
        $this->assertSame($updatedMedia->preview, 'random-name.jpg');
        $this->assertSame($updatedMedia->type, $originalMedia->type);
    }

    public function testDeleteMedia(): void
    {
        /** @var \Imahmood\FileStorage\Models\Media $media */
        $media = Media::factory()->create();

        $result = $this->fileStorage->delete($media);
        $this->assertTrue($result);
    }
}
