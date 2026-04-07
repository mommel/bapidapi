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
        Schema::create('parking_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Address details
            $table->string('address_street');
            $table->string('address_postal_code');
            $table->string('address_city');
            $table->string('address_state')->nullable();
            $table->string('address_country_code', 2);
            
            // Coordinates
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            
            $table->string('security_level');
            $table->json('amenities')->nullable(); // Array of amenity codes
            $table->json('opening_hours')->nullable();
            $table->json('capacity')->nullable();
            
            $table->string('operator_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('check_in_instructions')->nullable();
            
            $table->json('pricing')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_lots');
    }
};
