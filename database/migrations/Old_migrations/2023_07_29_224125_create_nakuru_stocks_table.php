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
        Schema::create('nakuru_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store')->default(1);
            $table->unsignedBigInteger('product');
            $table->string('properties')->nullable();
            $table->integer('price')->nullable();
            $table->boolean('deleted')->default(0);
            $table->boolean('deleted_by')->default(0)->nullable();
            $table->boolean('received')->default(0);
            $table->dateTime('transferred_date')->nullable();
            $table->boolean('returned')->default(0);
            $table->dateTime('returned_date')->nullable();
            $table->boolean('transferred')->default(0);
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->integer('buying_price')->nullable();
            $table->string('serial')->nullable();
            $table->boolean('sold')->default(0);
            $table->integer('broker')->default(0);
            $table->foreign('product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('store')->references('id')->on('stores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nakuru_stocks');
    }
};
