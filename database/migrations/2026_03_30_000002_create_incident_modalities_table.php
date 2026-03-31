<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_modalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_type_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['incident_type_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_modalities');
    }
};
