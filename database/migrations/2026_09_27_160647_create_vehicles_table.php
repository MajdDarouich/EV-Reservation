<?php

use App\Models\User;
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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->char('vin', 17)->unique();
            $table->string('license_plate')->unique();
            $table->string('manufacturer');
            $table->string('model');
            $table->year('model_year');
            $table->decimal('battery_capacity_kwh', 6, 2);
            $table->decimal('ac_max_kw', 6, 2);
            $table->decimal('dc_max_kw', 6, 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
