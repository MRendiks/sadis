<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->enum('decision', ['approve', 'reject', 'request_changes']);
            $table->text('notes')->nullable();
            $table->dateTime('created_at');

            $table->index('file_id', 'idx_reviews_file');
            $table->index('reviewer_id', 'idx_reviews_reviewer');
        });
    }
    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};
