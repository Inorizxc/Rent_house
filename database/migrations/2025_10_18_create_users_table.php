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
        Schema::create('users', function (Blueprint $table) {
            $table->id("user_id") ->unique();
            $table->foreignId("role_id")->constrained("roles",
            "role_id",
            "role_id")->onUpdate("cascade")->onDelete("cascade");
            $table->text("name");
            $table->text("sename");
            $table->text("patronymic")->nullable();
            $table->text("birth_date")->nullable();
            $table->text("email");
            $table->text("password");
            $table->text("phone")->nullable();
            $table->text("card")->nullable();
            $table->boolean("need_verification")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
