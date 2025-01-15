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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('fullName');
            $table->string('dob');
            $table->string('email');
            $table->string('password');
            $table->string('phoneNumber');
            $table->string('class');
            $table->string('parentName');
            $table->string('parentContact');
            $table->string('address');
            $table->string('role');
            $table->string('thumbnail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
