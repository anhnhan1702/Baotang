<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled_columns' => 'array',
        'is_mapview_enabled' => 'boolean',
    ];


    public function table() 
    {
        return $this->belongsTo(Table::class);
    }

    public function fields()
    {
        if(!$this->enabled_columns) {
            $fields = TableColumn::where('table_id', $this->table_id)->get();
        } else {
            $fields = TableColumn::whereIn('id', $this->enabled_columns)->get();
        }
        return $fields;
    }
}
