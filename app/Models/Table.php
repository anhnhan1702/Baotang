<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'table',
    ];

    /**
     * The forms that belong to the force app.
     */
    public function columns(): HasMany
    {
        return $this->hasMany(TableColumn::class);
    }

    /**
     * The forms that belong to the force app.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TableCategory::class, 'table_table_category');
    }
}
