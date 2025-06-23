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
        Schema::create('water_level_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_house_id')->constrained()->onDelete('cascade');
            $table->decimal('water_level', 5, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['pump_house_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_level_histories');
    }
};
