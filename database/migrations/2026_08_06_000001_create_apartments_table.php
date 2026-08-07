<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('apartment_number');            // e.g. 4-A
            $table->string('floor_label');                 // e.g. 4th Floor
            $table->unsignedInteger('floor_number');
            $table->unsignedInteger('size_sqft');
            $table->string('facing')->nullable();          // North / South
            $table->decimal('per_sqft_rate', 10, 2);
            $table->decimal('apartment_price', 14, 2);
            $table->decimal('car_parking_cost', 12, 2)->default(0);
            $table->decimal('bike_parking_cost', 12, 2)->default(0);
            $table->decimal('utility_connection_cost', 12, 2)->default(0);
            $table->decimal('gross_total', 14, 2);
            $table->string('status')->default('available'); // available | booked | sold
            $table->timestamps();

            $table->unique(['project_id', 'apartment_number']);
            $table->index(['project_id', 'status']);
            $table->index('floor_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
