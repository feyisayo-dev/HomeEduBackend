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
        Schema::create('offering', function (Blueprint $table) {
            $table->id();
            $table->string('parishcode');
            $table->string('parishname');
            $table->string('Amount');
            $table->string('receipt');
            $table->string('paidby');
            $table->string('paidfor');
            $table->string('pymtdate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offering');
    }
};
