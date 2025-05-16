<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('new_sale_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale');
            $table->unsignedBigInteger('stock');
            $table->integer('quantity');
            $table->integer('each');
            $table->integer('total');
            $table->integer('broker');
            $table->integer('returned_total');
            $table->integer('returned');
            $table->integer('replaced');
            $table->foreign('sale')->references('id')->on('sales');
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_sale_stocks');
    }
};
