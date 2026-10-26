<?php

use App\Enums\ReservationStatus;
use App\Models\Connector;
use App\Models\User;
use App\Models\Vehicle;
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
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Vehicle::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Connector::class)->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('arrival_deadline');
            $table->string('current_SOC_at_booking');
            $table->string('target_SOC');
            $table->decimal('energy_required_kWh', 8, 2); // system calculates
            $table->integer('estimated_duration_minutes'); // system calculates
            $table->time('estimated_end_time');
            $table->string('status')->default(ReservationStatus::CONFIRMED->value);
            $table->string('cancelled_by')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->dateTime('cancellation_deadline'); // if cancels after the deadline, it incereases the no-show count
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
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
