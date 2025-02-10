<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableSingleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'columns' => $this->columns,
            'categories' => TableCategoryResource::collection($this->categories),  
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
