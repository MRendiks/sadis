<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('folder_id')->nullable()->constrained('folders');
            $table->foreignId('uploader_id')->constrained('users');

            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('original_name', 255);
            $table->string('storage_disk', 50);
            $table->string('storage_path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->char('hash_sha256', 64)->nullable();

            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'archived'])->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('archived_at')->nullable();

            $table->integer('current_version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['division_id', 'status'], 'idx_files_division');
            $table->index('folder_id', 'idx_files_folder');
            $table->index('uploader_id', 'idx_files_uploader');
            $table->index('status', 'idx_files_status');
        });
    }
    public function down(): void {
        Schema::dropIfExists('files');
    }
};
