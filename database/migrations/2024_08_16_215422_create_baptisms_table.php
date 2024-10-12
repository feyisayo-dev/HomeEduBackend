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
        Schema::create('baptism', function (Blueprint $table) {  // Change 'baptism' to 'baptism'
            $table->id();
            $table->string('UserId');
            $table->string('Status');
            $table->string('Amount');
            $table->string('Year_of_Joining');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baptism');  // Change 'baptism' to 'baptisms'
    }
};
