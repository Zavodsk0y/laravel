<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request)
    {
        self::$wrap = 'body';

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Success',
            'name' => $this->name,
            'url' => url('/api-file') . "/{$this->file_id}",
            'file_id' => $this->file_id,
        ];
    }
}
