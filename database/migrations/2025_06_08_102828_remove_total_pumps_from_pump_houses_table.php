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
            $table->dropColumn('total_pumps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pump_houses', function (Blueprint $table) {
            $table->integer('total_pumps')->default(1)->after('pump_count');
        });
    }
};
