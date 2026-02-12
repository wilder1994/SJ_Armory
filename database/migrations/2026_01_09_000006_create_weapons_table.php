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
            $table->string('capacity')->nullable();
            $table->enum('ownership_type', [
                'company_owned',
                'leased',
                'third_party',
            ])->default('company_owned');
            $table->string('ownership_entity')->nullable();
            $table->string('permit_type')->nullable();
            $table->string('permit_number')->nullable();
            $table->date('permit_expires_at')->nullable();
            $table->unsignedBigInteger('permit_file_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('imprint_month', 7)->nullable();
            $table->foreignId('imprint_received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imprint_received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapons');
    }
};


