<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landowners', function (Blueprint $table) {
            $table->id();
            $table->string('land_locality');
            $table->string('land_address');
            $table->decimal('land_size_katha', 10, 2);
            $table->decimal('road_width_ft', 8, 2);
            $table->string('land_facing');
            $table->text('attractive_feature')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landowners');
    }
};
