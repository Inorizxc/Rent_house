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
            $table->string("house_id");
            $table->text("order_date");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('order_calendars');
    }
};
