<?php

use App\Models\Table;
use App\Models\TableCategory;
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
        Schema::create('table_table_category', function (Blueprint $table) {
            $table->foreignIdFor(Table::class)->constrained('tables')->onDelete('cascade');
            $table->foreignIdFor(TableCategory::class)->constrained('table_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_table_category');
    }
};
