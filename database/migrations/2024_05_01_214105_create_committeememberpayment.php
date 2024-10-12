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
        Schema::create('committeememberpayment', function (Blueprint $table) {
            $table->id();
            $table->string('committename');
            $table->string('parishcode');
            $table->string('parishname');
            $table->string('UserId');
            $table->string('paidfor');
            $table->string('paymentdate');
            $table->string('paidby');
            $table->string('amount');
            $table->string('receipt');
            $table->string('committeRefno');
	        $table->string('roleName');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committememberpayment');
    }
};
