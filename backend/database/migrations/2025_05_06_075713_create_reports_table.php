<?php

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
        // Drop the existing reports table if it exists
        Schema::dropIfExists('reports');

        // Create the reports table
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('report_type', ['daily', 'monthly', 'yearly']);
            $table->string('file_url')->nullable();
            $table->string('shop_name')->default('All Shops'); // Default to 'All Shops' for combined reports
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

