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
        Schema::create('debt_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock')->nullable();
            $table->unsignedBigInteger('old_stock')->nullable();
            $table->unsignedBigInteger('user')->nullable();
            $table->unsignedBigInteger('customer');
            $table->date('date');
            $table->boolean('pick')->nullable();
            $table->boolean('return')->nullable();
            $table->integer('quantity');
            $table->string('serial_number')->nullable();
            $table->integer('price');
            $table->integer('total_amount');
            $table->string('returned_by')->nullable();
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->foreign('old_stock')->references('id')->on('old_stocks');
            $table->foreign('user')->references('id')->on('users');
            $table->foreign('customer')->references('id')->on('customers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_histories');
    }
};
