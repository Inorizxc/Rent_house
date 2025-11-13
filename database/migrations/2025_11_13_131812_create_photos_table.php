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
        Schema::create('photos', function (Blueprint $table) {
            $table->id("photo_id");
            $table->foreignId("house_id")->constrained("houses","house_id","house_id")->onDelete("cascade");;
            $table->foreignId("user_id")->constrained("users","user_id","user_id")->onDelete("cascade");;
            $table->string("path");
            $table->string("name");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
