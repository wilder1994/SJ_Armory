<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->string('description', 100);
            $table->timestamps();

            $table->unique(['weapon_id', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_photos');
    }
};

