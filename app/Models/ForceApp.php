<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForceApp extends Model
{
    use HasFactory;

    /**
     * The forms that belong to the force app.
     */
    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'force_app_form');
    }

    /**
     * The reports that belong to the force app.
     */
    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'force_app_report');
    }
}
