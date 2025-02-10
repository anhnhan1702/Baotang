<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $categories = [];
        $categoryIds = [];
        if($this->categories) {
            $categories = implode(', ', $this->categories->pluck('name')->toArray());
            $categoryIds = $this->categories->pluck('id')->toArray();
        }
        $columns = [];
        if($this->columns) {
            foreach($this->columns as $column) {
                $columns[$column['column']] = [
                    'id' => $column['id'],
                    'name' => $column['name'],
                    'type' => $column['type'],
                ];
            }
        }
        return [
            'id' => $this->id,
            'value' => $this->id,
            'name' => $this->name,
            'label' => $this->name,
            'categories' => $categories,
            'category_ids' => $categoryIds, 
            'columns' => $columns, 
            'created_at' => $this->created_at,
        ];
    }
}
