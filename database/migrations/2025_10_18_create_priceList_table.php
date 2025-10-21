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
        Schema::create('price_lists', function (Blueprint $table) {
            $table->string("price_list_id") ->unique();
            $table->string("price");
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
