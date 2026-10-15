<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nid', 30)->nullable()->after('phone');
            $table->text('address')->nullable()->after('nid');
            $table->string('father_name', 150)->nullable()->after('address');
            $table->string('mother_name', 150)->nullable()->after('father_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nid', 'address', 'father_name', 'mother_name']);
        });
    }
};
