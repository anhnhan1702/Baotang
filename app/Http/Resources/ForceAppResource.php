<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForceAppResource extends JsonResource
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
            'value' => $this->id,
            'name' => $this->name,
            'label' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'forms' => FormResource::collection($this->forms),
            'reports' => ReportResource::collection($this->reports),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
