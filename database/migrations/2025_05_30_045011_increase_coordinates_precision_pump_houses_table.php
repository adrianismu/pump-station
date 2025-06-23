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
        Schema::table('pump_houses', function (Blueprint $table) {
            // Increase precision for latitude and longitude from decimal(10,7) to decimal(15,12)
            $table->decimal('lat', 15, 12)->change();
            $table->decimal('lng', 15, 12)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pump_houses', function (Blueprint $table) {
            // Revert back to original precision
            $table->decimal('lat', 10, 7)->change();
            $table->decimal('lng', 10, 7)->change();
        });
    }
};
