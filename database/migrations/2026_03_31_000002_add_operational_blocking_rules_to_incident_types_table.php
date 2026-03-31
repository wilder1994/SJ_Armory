<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('incident_types', 'blocks_operation') || !Schema::hasColumn('incident_types', 'persists_operational_block')) {
            Schema::table('incident_types', function (Blueprint $table) {
                if (!Schema::hasColumn('incident_types', 'blocks_operation')) {
                    $table->boolean('blocks_operation')->default(false)->after('sla_hours');
                }

                if (!Schema::hasColumn('incident_types', 'persists_operational_block')) {
                    $table->boolean('persists_operational_block')->default(false)->after('blocks_operation');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('incident_types')) {
            Schema::table('incident_types', function (Blueprint $table) {
                if (Schema::hasColumn('incident_types', 'persists_operational_block')) {
                    $table->dropColumn('persists_operational_block');
                }

                if (Schema::hasColumn('incident_types', 'blocks_operation')) {
                    $table->dropColumn('blocks_operation');
                }
            });
        }
    }
};