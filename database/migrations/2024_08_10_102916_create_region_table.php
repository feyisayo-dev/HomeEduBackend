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
        Schema::create('region', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('phone1');
            $table->string('phone2')->nullable();
            $table->string('country');
            $table->string('state');
            $table->string('city')->nullable();
            $table->string('address');
            $table->string('regionname');
            $table->string('nationalcode')->nullable();
            // $table->foreign('nationalcode')->references('code')->on('national')->onUpdate('cascade')->onDelete('set null');
            $table->string('rcode')->unique();
            $table->string('category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
