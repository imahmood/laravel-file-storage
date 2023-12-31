<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Imahmood\FileStorage\Models\Media
 */
class MediaEntityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'disk' => $this->disk,
            'file_name' => $this->file_name,
            'type' => $this->type,
            'preview_url' => $this->preview_url,
            'original_url' => $this->original_url,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->created_at->toIso8601String(),
        ];
    }
}
