<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            // Pesan teknis untuk internal (petugas/admin)
            $table->text('internal_message')->after('description')->nullable();

            // Pesan sederhana untuk publik (hanya jika severity 'critical' atau 'high')
            $table->text('public_message')->nullable()->after('internal_message');
            
            // Modify type enum to include weather_forecast if not exists
            // Note: This will be done via raw SQL if needed
        });
        
        // Update type enum to include weather_forecast
        DB::statement("ALTER TABLE alerts MODIFY COLUMN type ENUM('flood', 'maintenance', 'weather_forecast', 'water_level') DEFAULT 'flood'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn(['internal_message', 'public_message']);
        });
        
        // Restore original type enum
        DB::statement("ALTER TABLE alerts MODIFY COLUMN type ENUM('flood', 'maintenance') DEFAULT 'flood'");
    }
};
