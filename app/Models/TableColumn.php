<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableColumn extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table_id',
        'name',
        'description',
        'column',
        'type',
        'is_showonindex',
        'is_required',
        'is_searchable',
        'is_sortable',
        'is_unique',
        'relationship_table_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_showonindex' => 'boolean',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_sortable' => 'boolean',
        'is_unique' => 'boolean',
    ];
}
