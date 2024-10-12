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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->string('EventId');
            $table->string('Title');
            $table->string('Description')->nullable();
            $table->string('startdate');
            $table->string('enddate');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('Time');
            $table->string('Moderator')->nullable();
            $table->string('Minister')->nullable();
            $table->string('guest')->nullable();
            $table->string('location')->nullable();
            $table->string('Type');
            $table->string('parishcode');
            $table->string('parishname');
            $table->string('eventImg');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
