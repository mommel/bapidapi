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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fleet_number')->nullable();
            $table->string('type');
            $table->string('license_plate');
            $table->string('trailer_plate')->nullable();
            $table->boolean('adr')->default(false);
            $table->boolean('refrigerated')->default(false);
            $table->integer('height_cm')->nullable();
            $table->integer('length_cm')->nullable();
            $table->integer('weight_kg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
