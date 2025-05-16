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
        Schema::create('new_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer');
            $table->unsignedBigInteger('store');
            $table->unsignedBigInteger('user')->default(1);
            $table->unsignedBigInteger('stock')->nullable();
            $table->unsignedBigInteger('old_stock')->nullable();
            $table->date('date');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('total');
            $table->integer('returned')->default(0);
            $table->dateTime('returned_date')->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->string('picked_by')->nullable();
            $table->foreign('customer')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('stock')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('old_stock')->references('id')->on('old_stocks')->onDelete('cascade');
            $table->foreign('returned_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_invoices');
    }
};
