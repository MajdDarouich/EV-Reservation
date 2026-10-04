<?php

use App\Enums\StationStatus;
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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('latitude', 9, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('facilities')->nullable();
            $table->string('contact_info');
            $table->string('status')->default(StationStatus::ACTIVE->value);
            $table->unsignedSmallInteger('default_grace_period_minutes')->default(10);
            $table->decimal('default_overstay_fee_amount', 10, 2)->default(50.00)->unsigned();
            $table->unsignedSmallInteger('default_overstay_interval_minutes')->default(5);
            $table->unsignedSmallInteger('cancellation_window_minutes')->default(60);
            $table->unsignedSmallInteger('no_show_period_days')->default(30);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
