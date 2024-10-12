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
        Schema::create('committeemembers', function (Blueprint $table) {
            $table->id();
            $table->string('committeRefno');
            $table->string('committeName');
            $table->string('chairman');
            $table->string('chairperson');
            $table->string('secretary');
            $table->string('Fsecretary');
            $table->string('treasurer');
            $table->string('Mmembers');
            $table->string('Fmembers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committeemembers');
    }
};
