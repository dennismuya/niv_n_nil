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
        Schema::create('supply_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer');
            $table->integer('amount');
            $table->timestamps();
            $table->foreign('customer')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_payments');
    }
};
