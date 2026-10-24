<?php

use App\Enums\ConnectorStatus;
use App\Models\Charger;
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
        Schema::create('connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Charger::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('current_type');
            $table->integer('number')->unsigned()->nullable();
            $table->decimal('max_power_kw', 8, 2)->unsigned();
            $table->string('status')->default(ConnectorStatus::AVAILABLE->value);
            $table->timestamps();
            $table->unique(['charger_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connectors');
    }
};
