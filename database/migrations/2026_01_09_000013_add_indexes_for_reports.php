<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weapon_custodies', function (Blueprint $table) {
            $table->index(['custodian_user_id', 'is_active'], 'weapon_custodies_custodian_active_idx');
        });

        Schema::table('weapon_client_assignments', function (Blueprint $table) {
            $table->index(['client_id', 'is_active'], 'weapon_client_assignments_client_active_idx');
        });

        Schema::table('weapon_documents', function (Blueprint $table) {
            $table->index('valid_until');
            $table->index('revalidation_due_at');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('weapon_custodies', function (Blueprint $table) {
            $table->dropIndex('weapon_custodies_custodian_active_idx');
        });

        Schema::table('weapon_client_assignments', function (Blueprint $table) {
            $table->dropIndex('weapon_client_assignments_client_active_idx');
        });

        Schema::table('weapon_documents', function (Blueprint $table) {
            $table->dropIndex(['valid_until']);
            $table->dropIndex(['revalidation_due_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
