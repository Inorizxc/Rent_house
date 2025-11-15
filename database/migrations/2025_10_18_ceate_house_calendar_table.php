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
        Schema::create('house_calendar', function (Blueprint $table) {
            $table->id("house_calendar_id") ->unique();
            $table->foreignId("house_id")->constrained("houses","house_id","house_id")->onDelete("cascade")->onUpdate("cascade");
            $table->text("first_date");
            $table->text("second_date");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('house_calendars');
    }
};
