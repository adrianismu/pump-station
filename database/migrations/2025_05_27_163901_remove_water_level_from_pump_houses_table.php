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
            $table->dropColumn('water_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pump_houses', function (Blueprint $table) {
            $table->decimal('water_level', 5, 2)->nullable()->after('longitude');
        });
    }
};
