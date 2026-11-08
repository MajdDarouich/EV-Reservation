<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->time('actual_end_time')->nullable()->change();
            $table->timestamp('last_simulated_at')->nullable();
            $table->timestamp('target_reached_at')->nullable();
            $table->decimal('energy_cost', 12, 2)->default(0);
            $table->decimal('overstay_fee', 12, 2)->default(0);
            $table->decimal('final_cost', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'last_simulated_at',
                'target_reached_at',
                'energy_cost',
                'overstay_fee',
                'final_cost',
            ]);
        });
    }
};
