<?php

use App\Models\Table;
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
        Schema::create('table_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Table::class)->constrained('tables')->onDelete('cascade');
            $table->string('name');
            $table->string('column');
            $table->string('description')->nullable();
            $table->string('type');
            $table->foreignId('relationship_table_id')->nullable();
            $table->boolean('is_showonindex')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_sortable')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_columns');
    }
};
