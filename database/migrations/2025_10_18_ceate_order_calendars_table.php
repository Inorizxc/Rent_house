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
        Schema::create('order_calendars', function (Blueprint $table) {
            $table->id("order_calendar_id") ->unique();
            $table->foreignId("house_id")->constrained("houses",
        "house_id",
        "house_id")->onDelete("cascade")->onUpdate("cascade");
            $table->text("order_date");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('order_calendars');
    }
};
