<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('apartment_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            $table->decimal('property_price', 14, 2)->default(0)->after('unit_number'); // gross total at booking time
            $table->decimal('down_payment', 14, 2)->default(0)->after('booking_amount');
            $table->string('payment_type')->default('installment')->after('down_payment'); // installment | full
            $table->unsignedInteger('plan_months')->nullable()->after('payment_type');     // 12 | 24 | 36
            $table->decimal('surcharge_rate', 5, 2)->default(0)->after('plan_months');     // % additional on remaining
            $table->decimal('surcharge_amount', 14, 2)->default(0)->after('surcharge_rate');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('surcharge_amount');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->date('last_reminder_sent_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('apartment_id');
            $table->dropColumn([
                'property_price',
                'down_payment',
                'payment_type',
                'plan_months',
                'surcharge_rate',
                'surcharge_amount',
                'discount_amount',
            ]);
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });
    }
};
