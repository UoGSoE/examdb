<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GdprUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'papers' => GdprPaperResource::collection($this->papers),
        ];
    }
}
