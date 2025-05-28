<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) { // Cek jika kolom ada sebelum drop
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Definisikan ulang kolom jika migrasi di-rollback
            // Sesuaikan dengan definisi asli di SQL Anda jika berbeda
            $table->string('role')->nullable()->after('remember_token'); // Atau posisi aslinya
        });
    }
};