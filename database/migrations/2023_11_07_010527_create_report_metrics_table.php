<?php

use App\Models\Table;
use App\Models\TableColumn;
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
        Schema::create('report_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('math_type');
            $table->string('formula')->nullable();
            $table->foreignIdFor(Table::class)->constrained('tables')->onDelete('cascade');
            $table->foreignIdFor(TableColumn::class)->constrained('table_columns')->onDelete('cascade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_metrics');
    }
};
