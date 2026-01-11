<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_custodies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custodian_user_id')->constrained('users');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_active')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('support_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamps();

            $table->unique(['weapon_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_custodies');
    }
};
