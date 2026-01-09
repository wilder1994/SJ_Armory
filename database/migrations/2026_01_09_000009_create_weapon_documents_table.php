<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->enum('doc_type', [
                'ownership_support',
                'permit_or_authorization',
                'revalidation',
                'maintenance_record',
                'seizure_or_withdrawal',
                'decommission_record',
                'other',
            ]);
            $table->date('valid_until')->nullable();
            $table->date('revalidation_due_at')->nullable();
            $table->text('restrictions')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_documents');
    }
};
