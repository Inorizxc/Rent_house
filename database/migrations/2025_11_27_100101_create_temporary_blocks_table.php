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
        Schema::create('temporary_blocks', function (Blueprint $table) {
            $table->id("temporary_block_id")->unique();
            $table->foreignId("house_id")->constrained("houses", "house_id")->onDelete("cascade");
            $table->foreignId("user_id")->constrained("users", "user_id")->onDelete("cascade");
            $table->json("dates");
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_blocks');
    }
};
