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
        Schema::create('threshold_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'warning', 'critical', 'emergency'
            $table->string('label'); // 'Peringatan', 'Kritis', 'Darurat'
            $table->decimal('water_level', 5, 2); // Threshold ketinggian air
            $table->string('color', 7)->default('#fbbf24'); // Warna hex untuk UI
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threshold_settings');
    }
};
