<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('incident_types')) {
            return;
        }

        Schema::table('incident_types', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_types', 'requires_attachment')) {
                $table->boolean('requires_attachment')->default(false)->after('requires_modality');
            }

            if (!Schema::hasColumn('incident_types', 'requires_resolution_note')) {
                $table->boolean('requires_resolution_note')->default(false)->after('requires_attachment');
            }

            if (!Schema::hasColumn('incident_types', 'default_status')) {
                $table->string('default_status', 30)->default('open')->after('requires_resolution_note');
            }

            if (!Schema::hasColumn('incident_types', 'sla_hours')) {
                $table->unsignedSmallInteger('sla_hours')->nullable()->after('default_status');
            }

            if (!Schema::hasColumn('incident_types', 'blocks_operation')) {
                $table->boolean('blocks_operation')->default(false)->after('sla_hours');
            }

            if (!Schema::hasColumn('incident_types', 'persists_operational_block')) {
                $table->boolean('persists_operational_block')->default(false)->after('blocks_operation');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('incident_types')) {
            return;
        }

        Schema::table('incident_types', function (Blueprint $table) {
            foreach ([
                'persists_operational_block',
                'blocks_operation',
                'sla_hours',
                'default_status',
                'requires_resolution_note',
                'requires_attachment',
            ] as $column) {
                if (Schema::hasColumn('incident_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
