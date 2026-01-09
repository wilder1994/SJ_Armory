<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ADMIN', 'RESPONSABLE', 'AUDITOR'])->default('ADMIN')->after('password');
            $table->foreignId('position_id')->nullable()->after('role')->constrained('positions');
            $table->foreignId('responsibility_level_id')->nullable()->after('position_id')
                ->constrained('responsibility_levels');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('responsibility_level_id');
            $table->dropColumn('role');
        });
    }
};
