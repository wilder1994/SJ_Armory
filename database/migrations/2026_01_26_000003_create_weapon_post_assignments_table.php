<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_post_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->boolean('is_active')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['weapon_id', 'is_active']);
            $table->index(['post_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_post_assignments');
    }
};
