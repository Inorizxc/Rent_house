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
            $table->string("HouseId") ->unique();
            $table->string("RentDealerId");
            $table->text("PriceId");
            $table->text("RentTypeId");
            $table->text("HouseTypeId");
            $table->text("CalendarId");
            $table->text("Adress");
            $table->text("Area");
            $table->text("Deleted");
            $table->text("Ing");
            $table->text("Lat");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
