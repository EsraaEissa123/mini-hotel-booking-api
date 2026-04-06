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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('status'); // enum active|inactive (will use PHP Enum)
            $table->softDeletes();
            $table->timestamps();

            $table->index(['city', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
