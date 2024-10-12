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
        Schema::create('adultharvest', function (Blueprint $table) {
            $table->id();
            $table->string('UserId');
            $table->string('FullName');
            $table->string('pymtdate');
            $table->string('Amount');
            $table->string('parishcode');
            $table->string('parishname');
            $table->string('pymtImg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adultharvest');
    }
};
