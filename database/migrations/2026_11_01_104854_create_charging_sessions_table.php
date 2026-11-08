<?php

use App\Enums\ChargingStatus;
use App\Models\Reservation;
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
        Schema::create('charging_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Reservation::class)->nullable()->constrained()->nullOnDelete();
            $table->time('actual_start_time');
            $table->time('actual_end_time')->nullable();
            $table->string('start_SOC');
            $table->string('current_SOC');
            $table->string('target_SOC');
            $table->decimal('energy_delivered_kWh', 8, 2);
            $table->decimal('actual_power_kW', 8, 2);
            $table->string('charging_status')->default(ChargingStatus::IN_PROGRESS->value);
            $table->time('estimated_end_time')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charging_sessions');
    }
};
