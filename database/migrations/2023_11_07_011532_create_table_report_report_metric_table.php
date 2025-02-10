<?php

use App\Models\Report;
use App\Models\ReportMetric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_report_metric', function (Blueprint $table) {
            $table->foreignIdFor(Report::class)->constrained('reports')->onDelete('cascade');
            $table->foreignIdFor(ReportMetric::class)->constrained('report_metrics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_report_report_metric');
    }
};
