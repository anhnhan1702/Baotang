<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableColumnResource extends JsonResource
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
            'table_id' => $this->table_id,
            'value' => $this->value,
            'name' => $this->name,
            'label' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'is_showonindex' => $this->is_showonindex,
            'is_required' => $this->is_required,
            'is_searchable' => $this->is_searchable,
            'is_sortable' => $this->is_sortable,
            'is_unique' => $this->is_unique,
            'column' => $this->column,
            'relationship_table_id' => $this->relationship_table_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
