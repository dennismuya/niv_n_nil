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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number');
            $table->date('quotation_date');
            $table->unsignedBigInteger('user');
            $table->string('recipient');
            $table->string('recipient_phone');
            $table->string('recipient_box');
            $table->boolean('include_vat')->default(true);
            $table->integer('tax_percentage')->default(16);
            $table->unsignedBigInteger('store');
            $table->foreign('store')->references('id')->on('stores');
            $table->foreign('user')->references('id')->on('stores');
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('quotation');
            $table->json('properties')->nullable();
            $table->string('notes')->nullable();
            $table->foreign('stock')->references('id')->on('new_stocks');
            $table->foreign('quotation')->references('id')->on('quotations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
