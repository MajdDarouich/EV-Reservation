<?php

use App\Enums\ChargerStatus;
use App\Models\ChargingType;
use App\Models\Station;
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
        Schema::create('chargers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Station::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ChargingType::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->string('serial_number');
            $table->string('manufacturer');
            $table->string('model');
            $table->string('firmware_version');
            $table->string('status')->default(ChargerStatus::AVAILABLE->value);
            $table->decimal('default_overstay_fee_amount', 10, 2)->default(50.00)->unsigned();
            $table->unsignedSmallInteger('default_overstay_interval_minutes')->default(5);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargers');
    }
};
