<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code')->unique();
            $table->string('serial_number')->unique();
            $table->string('weapon_type');
            $table->string('caliber');
            $table->string('brand');
            $table->string('model');
            $table->enum('operational_status', [
                'in_armory',
                'assigned',
                'in_transit',
                'in_maintenance',
                'seized_or_withdrawn',
                'decommissioned',
            ])->default('in_armory');
            $table->enum('ownership_type', [
                'company_owned',
                'leased',
                'third_party',
            ])->default('company_owned');
            $table->string('ownership_entity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapons');
    }
};
