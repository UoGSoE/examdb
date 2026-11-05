<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GdprPaperResource extends JsonResource
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
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'filename' => $this->original_filename,
            'course' => $this->course->code,
            'comments' => GdprCommentResource::collection($this->comments),
        ];
    }
}
