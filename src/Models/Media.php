<?php

declare(strict_types=1);

namespace Imahmood\FileStorage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Imahmood\FileStorage\Database\Factories\MediaFactory;

/**
 * @property int $id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $file_name
 * @property string|null $preview
 * @property int $type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string|null $dir_relative_path
 * @property-read string|null $dir_absolute_path
 * @property-read string|null $original_relative_path
 * @property-read string|null $original_absolute_path
 * @property-read string|null $original_url
 * @property-read string|null $original_signed_url
 * @property-read string|null $preview_relative_path
 * @property-read string|null $preview_absolute_path
 * @property-read string|null $preview_url
 * @property-read string|null $preview_signed_url
 * @property-read bool $is_image
 * @property-read bool $is_pdf
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Imahmood\FileStorage\Models\Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Imahmood\FileStorage\Models\Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Imahmood\FileStorage\Models\Media query()
 */
class Media extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'type',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function newFactory(): MediaFactory
    {
        return MediaFactory::new();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected function fileName(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                $fileName = sha1($value.microtime());
                $ext = pathinfo($value, PATHINFO_EXTENSION);

                return "$fileName.$ext";
            },
        );
    }

    protected function dirRelativePath(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? $this->id.DIRECTORY_SEPARATOR : null,
        );
    }

    protected function dirAbsolutePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id ? Storage::disk($this->diskName())->path($this->dir_relative_path) : null;
            },
        );
    }

    protected function originalRelativePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id ? $this->dir_relative_path.$this->file_name : null;
            },
        );
    }

    protected function originalAbsolutePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id ? $this->dir_absolute_path.$this->file_name : null;
            },
        );
    }

    protected function originalUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id ? $this->makeUrl([$this->id, $this->file_name], false) : null;
            },
        );
    }

    protected function originalSignedUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id ? $this->makeUrl([$this->id, $this->file_name], true) : null;
            },
        );
    }

    protected function previewRelativePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id && $this->preview ? $this->dir_relative_path.$this->preview : null;
            },
        );
    }

    protected function previewAbsolutePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id && $this->preview ? $this->dir_absolute_path.$this->preview : null;
            },
        );
    }

    protected function previewUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id && $this->preview ? $this->makeUrl([$this->id, $this->preview], false) : null;
            },
        );
    }

    protected function previewSignedUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id && $this->preview ? $this->makeUrl([$this->id, $this->preview], true) : null;
            },
        );
    }

    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn () => is_string($this->file_name) && preg_match('/\.(webp|jpg|jpeg|png)$/i', $this->file_name),
        );
    }

    protected function isPdf(): Attribute
    {
        return Attribute::make(
            get: fn () => is_string($this->file_name) && str_ends_with($this->file_name, '.pdf'),
        );
    }

    private function diskName(): string
    {
        return config('file-storage.disk');
    }

    private function makeUrl(array $parameters, bool $signed): string
    {
        if (! $signed) {
            return URL::route('file-storage:public', $parameters);
        }

        $expireAt = now()->endOfDay();
        if ($expireAt->addMinutes(10)->isNextDay()) {
            $expireAt = now()->addDay()->endOfDay();
        }

        return URL::signedRoute('file-storage:private', $parameters, $expireAt);
    }
}
