<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $is_mapview_enabled = false;
        $fields = $this->fields();
        foreach($fields as $field) {
            if($field->type === 'location') {
                $is_mapview_enabled = true;
            }
            if($field->type === 'location_array') {
                $is_mapview_enabled = true;
            }
        }
        return [
            'id' => $this->id,
            'value' => $this->id,
            'table_id' => $this->table_id,
            'table_name' => $this->table->name,
            'table' => new TableResource($this->table),
            'name' => $this->name,
            'label' => $this->name,
            'description' => $this->description,
            'is_mapview_enabled' => $is_mapview_enabled,
            'template' => $this->template,
            'fields' => TableColumnResource::collection($fields),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
