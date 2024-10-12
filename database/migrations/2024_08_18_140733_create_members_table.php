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
        Schema::create('member', function (Blueprint $table) {
            $table->id();
            $table->string('UserId');
            $table->string('email');
            $table->string('password');
            $table->string('sname');
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('Gender');
            $table->string('dob');
            $table->string('mobile');
            $table->string('Altmobile')->nullable();
            $table->string('Residence');
            $table->string('Country');
            $table->string('State');
            $table->string('City');
            $table->string('Title');
            $table->string('dot')->nullable();
            $table->string('MStatus');
            $table->string('ministry');
            $table->string('Status')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('parishcode');
            $table->string('parishname');
            $table->string('role');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member');
    }
};
