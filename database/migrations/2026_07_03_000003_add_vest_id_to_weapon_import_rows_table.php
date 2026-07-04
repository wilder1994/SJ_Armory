<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weapon_import_rows', function (Blueprint $table) {
            $table->foreignId('vest_id')->nullable()->after('weapon_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('weapon_import_rows', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vest_id');
        });
    }
};
