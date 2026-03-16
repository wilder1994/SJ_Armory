<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('weapon_import_batches')->cascadeOnDelete();
            $table->foreignId('weapon_id')->nullable()->constrained('weapons')->nullOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('action');
            $table->string('summary')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('before_payload')->nullable();
            $table->json('after_payload')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'action']);
            $table->index(['batch_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_import_rows');
    }
};
