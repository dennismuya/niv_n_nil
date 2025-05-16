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
        Schema::create('customer_supplies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('customer');
            $table->foreign('stock')->references('id')->on('stocks');
            $table->foreign('customer')->references('id')->on('customers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_supplies');

    }
};
