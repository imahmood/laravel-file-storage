<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Tests;

use Illuminate\Support\Facades\Queue;
use Imahmood\FileStorage\FileStorage;
use Imahmood\FileStorage\Jobs\RunModifiersJob;
use Imahmood\FileStorage\Tests\TestSupport\Enums\TestDocumentType;
use Imahmood\FileStorage\Tests\TestSupport\Models\TestUserModel;
use Imahmood\FileStorage\Tests\TestSupport\UploadedFile;

class ManipulatorTest extends TestCase
{
    public function test_modifiers_job_pushed_after_media_created(): void
    {
        Queue::fake();
        config()->set('file-storage.queue_modifiers', true);

        $fileStorage = app(FileStorage::class);
        $fileStorage->create(
            type: TestDocumentType::AVATAR,
            relatedTo: new TestUserModel,
            uploadedFile: UploadedFile::fake('jpg'),
        );

        Queue::assertPushed(RunModifiersJob::class);
    }

    public function test_modifiers_job_not_pushed_after_media_created(): void
    {
        Queue::fake();
        config()->set('file-storage.queue_modifiers', false);

        $fileStorage = app(FileStorage::class);
        $fileStorage->create(
            type: TestDocumentType::AVATAR,
            relatedTo: new TestUserModel,
            uploadedFile: UploadedFile::fake('jpg'),
        );

        Queue::assertNotPushed(RunModifiersJob::class);
    }

    public function test_modifiers_run_after_media_created(): void
    {
        config()->set('file-storage.queue_modifiers', false);

        $fileStorage = app(FileStorage::class);
        $media = $fileStorage->create(
            type: TestDocumentType::AVATAR,
            relatedTo: new TestUserModel,
            uploadedFile: UploadedFile::fake('heic'),
        );

        $this->assertSame($media->type, TestDocumentType::AVATAR->value);
        $this->assertNotNull($media->preview);
        $this->assertStringEndsWith('.jpg', $media->file_name);
    }
}
