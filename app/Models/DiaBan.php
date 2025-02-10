<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaBan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'level',
    ];

    public function children()
    {
        return $this->hasMany(DiaBan::class, 'parent_id', 'id');
    }

    public function to_chucs()
    {
        return $this->hasMany(ToChuc::class);
    }
}
