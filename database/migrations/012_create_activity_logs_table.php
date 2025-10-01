<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject_type', 100);
            $table->bigInteger('subject_id');
            $table->string('action', 100);
            $table->foreignId('causer_id')->nullable()->constrained('users');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->dateTime('created_at');

            $table->index(['subject_type', 'subject_id'], 'idx_logs_subject');
            $table->index('action', 'idx_logs_action');
            $table->index('causer_id', 'idx_logs_causer');
        });
    }
    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
