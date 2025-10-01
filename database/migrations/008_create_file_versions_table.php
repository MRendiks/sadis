<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('file_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files');
            $table->integer('version');
            $table->string('storage_path', 500);
            $table->string('storage_disk', 50);
            $table->bigInteger('size_bytes')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->char('hash_sha256', 64)->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->dateTime('uploaded_at');
            $table->string('notes', 255)->nullable();

            $table->unique(['file_id', 'version'], 'uq_file_version');
            $table->index('file_id', 'idx_file_versions_file');
        });
    }
    public function down(): void {
        Schema::dropIfExists('file_versions');
    }
};
