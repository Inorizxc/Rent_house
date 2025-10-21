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
            $table->id("house_id") ->unique();
            $table->string("user_id");
            $table->text("price_id");
            $table->text("rent_type_id");
            $table->text("house_type_id");
            $table->text("calendar_id");
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
