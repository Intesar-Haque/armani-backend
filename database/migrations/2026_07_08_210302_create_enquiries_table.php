<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('project_slug')->nullable();
            $table->string('project_name')->nullable();
            $table->string('budget_range')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('project_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
