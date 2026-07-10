<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('label');
            $table->decimal('amount_due', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'sequence']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
