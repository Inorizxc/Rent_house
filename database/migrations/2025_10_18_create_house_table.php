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
        Schema::create('houses', function (Blueprint $table) {
            $table->id("house_id");
            $table->foreignId("user_id")
            ->constrained("users","user_id")
            ->onDelete("cascade");
            $table->text("price_id");
            $table->text("rent_type_id");
            $table->text("house_type_id");
            $table->foreignID("calendar_id")->nullable();
            $table->text("adress");
            $table->text("area");
            $table->text("is_deleted");
            $table->text("lng");
            $table->text("lat");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
