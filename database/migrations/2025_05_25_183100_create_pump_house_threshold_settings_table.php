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
        Schema::create('pump_house_threshold_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_house_id')->constrained()->onDelete('cascade');
            $table->string('name'); // 'warning', 'critical', 'emergency'
            $table->string('label'); // 'Peringatan', 'Kritis', 'Darurat'
            $table->decimal('water_level', 5, 2); // Threshold ketinggian air
            $table->string('color', 7)->default('#fbbf24'); // Warna hex untuk UI
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // Unique constraint untuk pump_house_id + name
            $table->unique(['pump_house_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pump_house_threshold_settings');
    }
};
