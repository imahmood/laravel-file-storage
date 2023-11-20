<?php
declare(strict_types=1);

namespace Imahmood\FileStorage\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Imahmood\FileStorage\Models\Media
 */
class PrivateMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'preview_url' => $this->preview_signed_url,
            'original_url' => $this->original_signed_url,
        ];
    }
}
