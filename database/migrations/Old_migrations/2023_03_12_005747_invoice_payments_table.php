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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice');
            $table->integer('cash')->default(0);
            $table->string('mpesa_ref')->nullable();
            $table->unsignedBigInteger('customer')->nullable();
            $table->integer('total_payment');
            $table->integer('mpesa')->default(0);
            $table->foreign('invoice')->references('id')->on('invoices');
            $table->foreign('customer')->references('id')->on('customers');

            $table->timestamps();

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
