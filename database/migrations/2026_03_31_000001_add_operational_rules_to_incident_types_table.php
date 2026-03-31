<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            $table->boolean('requires_attachment')->default(false)->after('requires_modality');
            $table->boolean('requires_resolution_note')->default(false)->after('requires_attachment');
            $table->string('default_status', 30)->default('open')->after('requires_resolution_note');
            $table->unsignedSmallInteger('sla_hours')->nullable()->after('default_status');
        });
    }

    public function down(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            $table->dropColumn([
                'requires_attachment',
                'requires_resolution_note',
                'default_status',
                'sla_hours',
            ]);
        });
    }
};
