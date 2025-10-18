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
        Schema::create('ordercalendars', function (Blueprint $table) {
            $table->string("IdOrderCalendar") ->unique();
            $table->string("IdHouse");
            $table->text("OrderDate");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ordercalendars');
    }
};
