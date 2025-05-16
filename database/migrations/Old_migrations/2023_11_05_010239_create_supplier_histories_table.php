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
        Schema::create('supplier_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->integer('quantity');
            $table->unsignedBigInteger('supplier')->nullable();
            $table->integer('buying_price')->nullable();
            $table->date('supply_date');
            $table->unsignedBigInteger('received_by');
            $table->string('serial_number')->nullable();
            $table->json('serial_number_array')->nullable();
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->foreign('supplier')->references('id')->on('customers');
            $table->foreign('received_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_histories');
    }
};
