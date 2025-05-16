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
        Schema::create('transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('origin_store');
            $table->unsignedBigInteger('destination_store');
            $table->date('transfer_date');
            $table->integer('transferred_quantity');
            $table->integer('received_quantity')->default(0);
            $table->integer('pedding_quantity')->default(0);
            $table->unsignedBigInteger('transferred_by');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->json('transfer_serial_array')->nullable();
            $table->string('transfer_serials')->nullable();
            $table->foreign('origin_store')->references('id')->on('stores');
            $table->foreign('destination_store')->references('id')->on('stores');
            $table->foreign('transferred_by')->references('id')->on('users');
            $table->foreign('received_by')->references('id')->on('users');
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_histories');
    }
};
