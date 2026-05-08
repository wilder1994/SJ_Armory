<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('weapons', 'permit_authenticated_file_id')) {
            // MySQL: hay que ejecutar DROP FOREIGN KEY explícito; Blueprint::dropForeign a veces no elimina la FK en hosting compartido.
            $connection = Schema::getConnection();
            if ($connection->getDriverName() === 'mysql') {
                $db = $connection->getDatabaseName();
                $fkName = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', $db)
                    ->where('TABLE_NAME', 'weapons')
                    ->where('COLUMN_NAME', 'permit_authenticated_file_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fkName) {
                    DB::statement('ALTER TABLE `weapons` DROP FOREIGN KEY `'.$fkName.'`');
                }
            } else {
                Schema::table('weapons', function (Blueprint $table) {
                    $table->dropForeign(['permit_authenticated_file_id']);
                });
            }

            Schema::table('weapons', function (Blueprint $table) {
                $table->dropColumn('permit_authenticated_file_id');
            });
        }

        if (! Schema::hasTable('permit_authenticated_templates')) {
            Schema::create('permit_authenticated_templates', function (Blueprint $table) {
                $table->id();
                $table->string('permit_kind', 20)->unique();
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_authenticated_templates');

        Schema::table('weapons', function (Blueprint $table) {
            $table->foreignId('permit_authenticated_file_id')
                ->nullable()
                ->after('permit_file_id')
                ->constrained('files')
                ->nullOnDelete();
        });
    }
};
