<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_divisions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('division_id')->constrained('divisions');
            $table->primary(['user_id', 'division_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('user_divisions');
    }
};
