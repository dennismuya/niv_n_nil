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
        //

        Schema::create('new_stock_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->boolean('need_serial')->default(false);
            $table->timestamps();
        });

        Schema::create('new_stock_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->boolean('serial_number')->default(false);
            $table->boolean('sold')->default(false);
            $table->boolean('on_invoice')->default(false);
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->timestamps();
        });

        Schema::table('new_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('new_stock_category')->nullable();
            $table->integer('minimum_price')->default(0);
            $table->foreign('new_stock_category')->on('new_stock_categories')->references('id');
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
