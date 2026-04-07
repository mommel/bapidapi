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
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reservation_number')->unique();
            
            $table->uuid('parking_lot_id');
            $table->uuid('driver_id')->nullable();
            $table->uuid('vehicle_id')->nullable();
            
            $table->string('status');
            $table->timestamp('check_in');
            $table->timestamp('check_out');
            
            $table->string('access_code')->nullable();
            $table->decimal('total_price_amount', 10, 2)->nullable();
            $table->string('total_price_currency', 3)->nullable();
            
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->foreign('parking_lot_id')->references('id')->on('parking_lots')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
