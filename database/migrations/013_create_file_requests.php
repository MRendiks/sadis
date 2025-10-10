<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('requester_id');
            $table->string('original_name', 255);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('temp_disk', 50)->default('local');
            $table->string('temp_path', 500);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('folder_id', 'fk_fr_folder')
                ->references('id')->on('folders')
                ->onDelete('cascade');

            $table->foreign('requester_id', 'fk_fr_requester')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('reviewed_by', 'fk_fr_reviewer')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id')->nullable()->after('id');

            $table->foreign('request_id', 'fk_files_request')
                ->references('id')->on('file_requests')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign('fk_files_request');
            $table->dropColumn('request_id');
        });

        Schema::dropIfExists('file_requests');
    }
};
