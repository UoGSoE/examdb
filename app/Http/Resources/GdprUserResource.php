<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GdprUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'username' => $this->username,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'papers' => GdprPaperResource::collection($this->papers),
        ];
    }
}
