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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('stock_action');
            $table->integer('buying_price')->nullable();
            $table->integer('selling_price')->nullable();
            $table->integer('quantity');
            $table->unsignedBigInteger('supplier')->nullable();
            $table->dateTime('action_date');
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('store');
            $table->integer('previous_stock');
            $table->integer('stock_after');
            $table->string('reason')->nullable();
            $table->string('serial_number')->nullable();
            $table->json('serial_array')->nullable();
            $table->unsignedBigInteger('returned_from')->nullable();
            $table->boolean('replaced')->nullable();
            $table->unsignedBigInteger('sale_replaced')->nullable();
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->foreign('stock_action')->references('id')->on('stock_actions');
            $table->foreign('supplier')->references('id')->on('customers');
            $table->foreign('user')->references('id')->on('users');
            $table->foreign('store')->references('id')->on('stores');
            $table->foreign('returned_from')->references('id')->on('customers');
            $table->foreign('sale_replaced')->references('id')->on('sales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
