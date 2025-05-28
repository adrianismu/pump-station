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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_house_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('severity');
            $table->string('water_level')->nullable();
            $table->string('pump_status')->nullable();
            $table->string('rainfall')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('recipients')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};