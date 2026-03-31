<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_incident_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entry_type', 30)->default('note');
            $table->text('message');
            $table->dateTime('follow_up_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['weapon_incident_id', 'created_at'], 'weapon_incident_follow_ups_incident_created_idx');
            $table->index(['entry_type', 'follow_up_at'], 'weapon_incident_follow_ups_type_follow_up_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_incident_follow_ups');
    }
};
