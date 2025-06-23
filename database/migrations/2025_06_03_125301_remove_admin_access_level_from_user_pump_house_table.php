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
        // Update existing 'admin' records to 'write'
        DB::table('user_pump_house')
            ->where('access_level', 'admin')
            ->update(['access_level' => 'write']);

        // Modify the enum to remove 'admin'
        DB::statement("ALTER TABLE user_pump_house MODIFY COLUMN access_level ENUM('read', 'write') DEFAULT 'read'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the enum with 'admin'
        DB::statement("ALTER TABLE user_pump_house MODIFY COLUMN access_level ENUM('read', 'write', 'admin') DEFAULT 'read'");
    }
};
