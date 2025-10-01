<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('parent_id')->nullable()->constrained('folders');
            $table->string('name', 150);
            $table->string('slug', 200);
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['division_id', 'slug'], 'uq_division_slug');
            $table->index('parent_id', 'idx_folder_parent');
        });
    }
    public function down(): void {
        Schema::dropIfExists('folders');
    }
};
