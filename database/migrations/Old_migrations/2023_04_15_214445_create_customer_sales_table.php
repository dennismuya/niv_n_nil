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
        Schema::create('customer_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer');
            $table->unsignedBigInteger('sale');
            $table->foreign('customer')->references('id')->on('customers');
            $table->foreign('sale')->references('id')->on('sales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sales');
    }
};
