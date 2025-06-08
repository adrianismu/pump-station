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
        Schema::table('alerts', function (Blueprint $table) {
            // Tipe pemicu alert
            $table->enum('type', ['water_level', 'weather_forecast'])->after('id')->default('water_level');

            // Tingkat keparahan untuk menentukan prioritas dan visibilitas publik
            $table->enum('severity', ['Siaga', 'Awas'])->after('type')->default('Siaga');

            // Pesan teknis untuk internal (petugas/admin)
            $table->text('internal_message')->after('severity');

            // Pesan sederhana untuk publik (hanya jika severity 'Awas')
            $table->text('public_message')->nullable()->after('internal_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn(['type', 'severity', 'internal_message', 'public_message']);
        });
    }
};
