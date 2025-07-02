<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type', 255);
            $table->integer('tokenable_id')->nullable();
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->string('abilities')->nullable();
            $table->string('last_used_at', 50)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('created_at', 50)->nullable();
            $table->string('updated_at', 50)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
