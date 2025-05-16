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

        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale');
            $table->unsignedBigInteger('stock');
            $table->foreign('sale')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('stock')->references('id')->on('stocks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
