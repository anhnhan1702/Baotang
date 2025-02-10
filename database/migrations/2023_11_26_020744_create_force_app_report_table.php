<?php

use App\Models\ForceApp;
use App\Models\Report;
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
        Schema::create('force_app_report', function (Blueprint $table) {
            $table->foreignIdFor(ForceApp::class)->constrained('force_apps')->onDelete('cascade');
            $table->foreignIdFor(Report::class)->constrained('reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('force_app_report');
    }
};
