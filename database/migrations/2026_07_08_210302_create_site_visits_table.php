<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('project_slug')->nullable();
            $table->string('project_name')->nullable();
            $table->dateTime('preferred_visit_at')->nullable();
            $table->unsignedSmallInteger('party_size')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('project_slug');
            $table->index('preferred_visit_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
