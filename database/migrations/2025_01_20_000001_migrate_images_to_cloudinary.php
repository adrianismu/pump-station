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
        // Menambahkan kolom cloudinary_id untuk tracking gambar yang diupload ke Cloudinary
        Schema::table('pump_houses', function (Blueprint $table) {
            $table->string('cloudinary_id')->nullable()->after('image');
        });

        Schema::table('education_contents', function (Blueprint $table) {
            $table->string('cloudinary_id')->nullable()->after('image');
            $table->string('infographic_cloudinary_id')->nullable()->after('infographic_url');
        });

        // Untuk reports, kita akan menggunakan JSON array untuk menyimpan cloudinary IDs
        Schema::table('reports', function (Blueprint $table) {
            $table->json('cloudinary_ids')->nullable()->after('images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pump_houses', function (Blueprint $table) {
            $table->dropColumn('cloudinary_id');
        });

        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropColumn(['cloudinary_id', 'infographic_cloudinary_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('cloudinary_ids');
        });
    }
}; 