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
        Schema::create('receive_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('origin_store');
            $table->unsignedBigInteger('destination_store');
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->unsignedBigInteger('received_by');
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->foreign('origin_store')->references('id')->on('stores');
            $table->foreign('destination_store')->references('id')->on('stores');
            $table->foreign('received_by')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_histories');
    }
};
