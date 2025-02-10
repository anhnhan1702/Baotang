<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends Model
{
    use HasFactory;

    /**
     * The report metrics that belong to the report.
     */
    public function report_metrics(): BelongsToMany
    {
        return $this->belongsToMany(ReportMetric::class, 'report_report_metric');
    }
}
