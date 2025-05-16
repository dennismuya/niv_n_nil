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
        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer');
            $table->unsignedBigInteger('invoice');
            $table->foreign('customer')->references('id')->on('customers');
            $table->foreign('invoice')->references('id')->on('invoices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invoices');
    }
};
