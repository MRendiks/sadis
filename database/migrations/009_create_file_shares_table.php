<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files');
            $table->enum('target_type', ['division', 'user', 'public']);
            $table->bigInteger('target_id')->nullable(); // tergantung target_type
            $table->enum('permission', ['read', 'write'])->default('read');
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->dateTime('created_at');

            $table->index('file_id', 'idx_shares_file');
            $table->index(['target_type', 'target_id'], 'idx_shares_target');
        });
    }
    public function down(): void {
        Schema::dropIfExists('file_shares');
    }
};
